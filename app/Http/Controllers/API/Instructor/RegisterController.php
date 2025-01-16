<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterInstructorRequest;
use App\Models\Education;
use App\Models\Profile;
use App\Traits\LoggableTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    use LoggableTrait;

    public function register(RegisterInstructorRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('member')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản không phù hợp để đăng ký làm giảng viên.',
                ], Response::HTTP_FORBIDDEN);
            }

            DB::beginTransaction();

            $validated = $request->validated();

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
