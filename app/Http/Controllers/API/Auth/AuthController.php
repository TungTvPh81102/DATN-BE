<?php

namespace App\Http\Controllers\API\Auth;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function forgotPassword(ForgotPassWordRequest $request)
    {
        // Kiểm tra email hợp lệ
        $request->validated();

<<<<<<< HEAD

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại'], 404);
        }

        // Tạo token reset ngẫu nhiên
        $token = Str::random(60);

        // Tạo URL đặt lại mật khẩu (không dùng bảng password_resets)
        $verificationUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

=======
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại'], 404);
        }

        // Tạo token reset ngẫu nhiên
        $token = Str::random(60);

        // Tạo URL đặt lại mật khẩu (không dùng bảng password_resets)
        $verificationUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
        // Gửi email
        Mail::to($user->email)->send(new ForgotPasswordEmail($verificationUrl));

        return response()->json([
            'success' => true,
            'message' => 'Email đặt lại mật khẩu đã được gửi!',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            //code...
            $data = $request->only('email', 'password', 'password_confirmation', 'token');

            $status = Password::reset(
                $data,
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    $user->token()->delete(); // Hủy token API cũ nếu dùng Sanctum
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ], 200);
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không được thay đổi thành công',
            ], 400);
        }
    }

    public function verifyEmail(VerifyEmailRequest $verifyEmailRequest)
    {
        try {
            //code...
            $user = User::where('email', $verifyEmailRequest->email)->first();

            if ($user || Hash::check($verifyEmailRequest->password, $user->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mật khẩu chính xác.',
                ], 200);
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không chính xác.',
            ], 400);
        }
    }

    public function signUp(SingupUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->only(['name', 'email', 'password', 'repassword']);
            $data['avatar'] = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';

            do {
                $data['code'] = str_replace('-', '', Str::uuid()->toString());
            } while (User::query()->where('code', $data['code'])->exists());

            $user = User::query()->create($data);

            $user->assignRole("member");
            $verificationUrl = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);

            Mail::to($user->email)->send(new VerifyEmail($verificationUrl));
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tạo tài khoản thành công, vui lòng đăng nhập',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout()
    {
        try {
            // Auth::user()->currentAccessToken()->delete();

            return $this->respondOk('Đăng xuất thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
