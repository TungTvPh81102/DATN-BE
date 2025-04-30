<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgotPassWordRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\SigninInstructorRequest;
use App\Http\Requests\API\Auth\SinginUserRequest;
use App\Http\Requests\API\Auth\SingupUserRequest;
use App\Http\Requests\API\Auth\VerifyEmailRequest;
use App\Mail\Auth\ForgotPasswordEmail;
use App\Mail\Auth\VerifyEmail;
use App\Models\Education;
use App\Models\Profile;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function forgotPassword(ForgotPassWordRequest $request)
    {
        try {
            $request->validated();

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->respondNotFound('Email không tồn tai');
            }

            if ($user->email_verified_at == null) {
                return $this->respondNotFound('Tài khoản chưa xác thực, vui lòng kiểm tra email');
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $token,
                    'created_at' => now()
                ]
            );

            $verificationUrl = config('app.fe_url') . '/reset/' . $token;

            Mail::to($user->email)->send(new ForgotPasswordEmail($verificationUrl, $user));

            return $this->respondOk('Gửi yêu cầu thành công, vui lòng kiểm tra email');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $tokenRecord = DB::table('password_reset_tokens')
                ->where('token', $data['token'])
                ->first();

            if (Carbon::parse($tokenRecord->created_at)->addMinutes(30)->isPast()) {
                return $this->respondNotFound('Liên kết đã hết hạn hoặc không tồn tại, vui lòng thử lại');
            }

            $user = User::query()->where('email', $tokenRecord->email)->first();

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $user->update([
                'password' => Hash::make($data['password'])
            ]);

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            DB::commit();

            return $this->respondOk('Thay đổi mật khẩu thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function verify(string $token)
    {
        try {
            $user = User::query()
                ->where('verification_token', $token)
                ->where('email_verified_at', null)
                ->where('created_at', '>', now()->subMinutes(30))
                ->first();

            if (!$user) {
                return redirect()->away(config('app.fe_url') . '/verify?status=error');
            }

            if ($user->email_verified_at !== null) {
                return $this->respondError('Tài khoản đã được xác thực');
            }

            $user->email_verified_at = now();
            $user->verification_token = null;
            $user->save();

            return redirect()->away(config('app.fe_url') . '/verify?status=success');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->away(config('app.fe_url') . '/verify?status=fail');
        }
    }

    public function signUp(SingupUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->only(['name', 'email', 'password', 'repassword']);
            $defaultAvatars = [
                'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png',
                'https://res.cloudinary.com/dvrexlsgx/image/upload/v1743316311/users/34wNL3FsA1.png',
            ];

            $data['avatar'] =  $defaultAvatars[array_rand($defaultAvatars)];
            $data['verification_token'] = Str::random(60);

            do {
                $uuid = strtolower(str_replace('-', '', Str::uuid()->toString()));
                $data['code'] = substr($uuid, 0, 10);
            } while (User::query()->where('code', $data['code'])->exists());

            $user = User::query()->create($data);

            $user->assignRole("member");
            $verificationUrl = config('app.url') . '/api/auth/verify/' . $user->verification_token;

            Mail::to($user->email)->send(new VerifyEmail($verificationUrl));

            DB::commit();

            return $this->respondSuccess('Đăng ký thành công, vui lòng kiểm tra email để xác nhận tài khoản');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function signIn(SinginUserRequest $request)
    {
        try {
            $data = $request->only(['email', 'password']);

            $user = User::query()->where('email', $data['email'])->first();

            if (is_null($user)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản không tồn tại, vui lòng thử lại!',
                    'user' => $user
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($user->status == "blocked") {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản đã bị khóa!',
                ], Response::HTTP_FORBIDDEN);
            }

            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản chưa xác thực, vui lòng kiểm tra email của bạn.',
                ], Response::HTTP_FORBIDDEN);
            }

            if (Auth::attempt($data)) {

                DB::beginTransaction();

                if ($user->status == "inactive") {
                    $user->status = "active";
                    $user->save();
                }

                $expiresAt = Carbon::now(env('APP_TIMEZONE'))->addMonth();

                $token = $user->createToken('API Token');

                $tokenInst = $token->accessToken;
                $tokenInst->expires_at = $expiresAt;
                $tokenInst->save();

                DB::commit();

                Cache::put('user_status_' . $user->id, true, now()->addMinutes(2));
                Cache::put("last_activity_$user->id", now(), now()->addMinutes(2));
                event(new UserStatusChanged($user->id, 'online'));

                $role = $user->roles->first()->name;

                return response()->json([
                    'message' => 'Đăng nhập thành công',
                    'user' => $user,
                    'role' => $role,
                    'token' => $token->plainTextToken,
                    'expires_at' => $expiresAt
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Mật khẩu không đúng'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();

            $userId = $user->id;

            $user->currentAccessToken()->delete();

            Cache::forget('user_status_' . $userId);
            Cache::forget('user_broadcast_' . $userId);

            event(new UserStatusChanged($user->id, 'offline'));

            return $this->respondOk('Đăng xuất thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getUserWithToken()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $role = $user->roles->first()->name;

            $response = [
                'user' => [
                    'id' => $user->id,
                    'code' => $user->code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar
                ],
                'role' => $role
            ];

            return $this->respondOk('Thông tin người dùng: ' . $user->name, $response);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
