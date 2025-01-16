<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgotPassWordRequest;
use App\Http\Requests\API\Auth\ResetPasswordRequest;
use App\Http\Requests\API\Auth\SigninInstructorRequest;
use App\Http\Requests\API\Auth\SinginUserRequest;
use App\Http\Requests\API\Auth\SingupUserRequest;
use App\Http\Requests\API\Auth\VerifyEmailRequest;
use App\Models\Education;
use App\Models\Profile;
use App\Models\User;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;

    public function forgotPassword(ForgotPassWordRequest $forgotPassWordRequest)
    {
        try {
            //code...
            $status = Password::sendResetLink($forgotPassWordRequest->only('email'));

            if($status === Password::RESET_LINK_SENT)
            {
                return response()->json([
                    'success' => true,
                    'message' => __($status)
                ],200);
            }

        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'message' => 'Không thể gửi liên kết đặt lại mật khẩu. Vui lòng thử lại.',
            ], 400);
        }
    }

    public function resetPassword(ResetPasswordRequest $resetPasswordRequest)
    {
        try {
            //code...
            $data = $resetPasswordRequest->only('email', 'password', 'password_confirmation', 'token');

            $status = Password::reset(
                $data,
                function($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->token()->delete(); // Hủy token API cũ nếu dùng Sanctum
            });

            if($status === Password::PASSWORD_RESET)
            {
                return response()->json([
                    'success' => true,
                    'message' => __($status),
                ], 200);
            }

        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'success' => false,
                'message' => __($status),
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

            $validated = $request->validated();

            $data = $request->only(['name', 'email', 'password', 'repassword']);
            $data['avatar'] = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';

            do {
                $data['code'] = str_replace('-', '', Str::uuid()->toString());
            } while (User::query()->where('code', $data['code'])->exists());

            $user = User::query()->create($data);

            $user->assignRole("member");

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
            $validated = $request->validated();

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

                return response()->json([
                    'status' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $user,
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

    public function registerInstructor(SigninInstructorRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $data = $request->only(['name', 'email', 'password', 'repassword']);
            $data['avatar'] = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png';

            do {
                $data['code'] = str_replace('-', '', Str::uuid()->toString());
            } while (User::query()->where('code', $data['code'])->exists());

            $user = User::query()->create($data);

            $user->assignRole("member");

            $dataProfiles = $request->only(['phone', 'address', 'experience']);
            $dataProfiles['bio'] = json_encode($request->bio);
            $dataProfiles['user_id'] = $user->id;

            $profile = Profile::query()->create($dataProfiles);

            $education = Education::query()->create([
                'name' => $user->name,
                'degree' => $request->degree,
                'major' => $request->major,
                'certificates' => json_encode($request->certificates),
                'qa_systems' => json_encode($request->qa_systems),
                'start_date' => now(env('APP_TIMEZONE')),
                'profile_id' => $profile->id,
            ]);

            $user->assignRole("instructor");

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Đăng ký giảng viên thành công!',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }
}
