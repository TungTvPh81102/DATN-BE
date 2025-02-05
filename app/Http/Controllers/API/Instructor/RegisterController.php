<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterInstructorRequest;
use App\Models\Approvable;
use App\Models\Career;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\RegisterInstructorNotification;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use LoggableTrait, ApiResponseHelpers, UploadToCloudinaryTrait;

    const FOLDER_CERTIFICATES = 'certificates';

    const URL_IMAGE_DEFAULT = "https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png";


    public function register(RegisterInstructorRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::check() ? Auth::user() : $this->createUser($request->validated());

            $uploadedCertificates = $this->uploadCertificates($request->file('certificates'));

            $qaSystemsData = $this->prepareQaSystemsData($request->qa_systems);

            $profile = $this->createProfile($user->id, $request->only(['phone', 'address', 'experience']), $uploadedCertificates, $qaSystemsData);

            $user->assignRole("instructor");

            $approvable = Approvable::where('approvable_id', $user->id)
                ->where('approvable_type', User::class)
                ->first();

            if (!$approvable) {
                $approvable = new Approvable();
                $approvable->approvable_id = $user->id;
                $approvable->approvable_type = User::class;
                $approvable->status = 'pending';
                $approvable->request_date = now();
                $approvable->save();
            } else {
                return $this->respondOk('Yêu cầu kiểm duyệt đã được gửi');
            }

            $managers = User::query()->role([
                'admin',
            ])->get();

            foreach ($managers as $manager) {
                $manager->notify(new RegisterInstructorNotification($user->load('approvables')));
            }

            DB::commit();

            return $this->respondCreated('Đăng ký giảng viên thành công', $user);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function createUser(array $validated)
    {
        return User::create([
            'name' => $validated['name'],
            'code' => Str::uuid()->toString(),
            'email' => $validated['email'],
            'password' => $validated['password'],
            'avatar' => self::URL_IMAGE_DEFAULT,
        ]);
    }

    private function createProfile(int $userId, array $data, array $certificates, array $qaSystemsData)
    {
        return Profile::query()->create(array_merge($data, [
            'user_id' => $userId,
            'certificates' => json_encode($certificates),
            'qa_systems' => json_encode($qaSystemsData),
        ]));
    }

    private function uploadCertificates($certificates)
    {
        if ($certificates) {
            return $this->uploadImageMultiple($certificates, self::FOLDER_CERTIFICATES);
        }
        return [];
    }

    private function prepareQaSystemsData($qaSystems)
    {
        return collect($qaSystems)->map(function ($qaSystem) {
            return [
                'question' => $qaSystem['question'],
                'selected_options' => $qaSystem['selected_options'],
                'options' => $qaSystem['options'],
            ];
        })->toArray();
    }
}
