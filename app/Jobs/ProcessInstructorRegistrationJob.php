<?php

namespace App\Jobs;

use App\Events\InstructorApproved;
use App\Models\Approvable;
use App\Models\InstructorCommission;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\InstructorApprovalNotification;
use App\Notifications\InstructorRejectedNotification;
use App\Notifications\RegisterInstructorNotification;
use App\Traits\UploadToLocalTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessInstructorRegistrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UploadToLocalTrait;

    protected $user;
    protected $requestData;

    public function __construct($user, $requestData)
    {
        $this->user = $user;
        $this->requestData = $requestData;
    }


    public function handle(): void
    {
        try {
            DB::beginTransaction();
            $user = $this->user;

            $this->createProfile($user->id, $this->requestData['certificates'], $this->requestData['qa_systems'], $this->requestData['identity_verification'] ?? null);

            $approvable = Approvable::create([
                'approvable_id' => $user->id,
                'approvable_type' => User::class,
                'status' => 'pending',
                'request_date' => now(),
            ]);

            $approvalCheck = $this->checkInstructorApproval($user);

            if ($approvalCheck['progress'] >= 70) {
                $approvable->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'note' => 'Duyệt đăng ký trở thành giảng viên.',
                    'approver_id' => null,
                    'approval_logs' => [
                        [
                            'name' => 'Hệ thống',
                            'status' => 'approved',
                            'note' => 'Giảng viên tự động được phê duyệt.',
                            'reason' => null,
                            'action_at' => now()->toISOString(),
                        ]
                    ]
                ]);

                $user->syncRoles(['instructor']);

                event(new InstructorApproved($user));

                $user->notify(new InstructorApprovalNotification($user));

                InstructorCommission::create([
                    'instructor_id' => $user->id,
                    'rate' => 0.6,
                    'rate_logs' => json_encode([
                        [
                            'old_rate' => null,
                            'new_rate' => 0.6,
                            'changed_at' => now()->toISOString(),
                            'user_name' => 'Hệ thống tự động đánh giá',
                            'note' => 'Tỷ lệ mặc định khi giảng viên bắt đầu tham gia'
                        ]
                    ])
                ]);
            } else {
                $approvable->update([
                    'status' => 'rejected',
                    'note' => 'Hồ sơ chưa đủ điều kiện duyệt. Vui lòng bổ sung thông tin.',
                    'rejected_at' => now(),
                    'approver_id' => null,
                    'approval_logs' => json_encode([
                        [
                            'name' => 'Hệ thống',
                            'status' => 'rejected',
                            'note' => 'Hồ sơ chưa đủ điều kiện duyệt. Vui lòng bổ sung thông tin.',
                            'reason' => json_encode($approvalCheck['errors']),
                            'action_at' => now()->toISOString(),
                        ]
                    ])
                ]);

                $user->notify(new InstructorRejectedNotification($user));
            }

            $managers = User::query()->role(['admin'])->get();
            foreach ($managers as $manager) {
                $manager->notify(new RegisterInstructorNotification($user->load('approvables')));
            }

            DB::commit();
        } catch (\Exception $e) {
            $this->logError($e);

            DB::rollBack();
        }
    }

    private function createProfile(int $userId, array $certificates, array $qaSystemsData, $indentityVerification = null)
    {
        $profile = Profile::query()->where('user_id', $userId)->first();

        $profileData = [
            'certificates' => json_encode($certificates),
            'qa_systems' => json_encode($qaSystemsData),
            'identity_verification' => $indentityVerification
        ];

        if ($profile) {
            $profile->update($profileData);
            return $profile;
        }

        $profileData['user_id'] = $userId;
        return Profile::query()->create($profileData);
    }

    private function checkInstructorApproval(User $instructor)
    {
        $profile = $instructor->profile;
        $errors = [];
        $pass = [];

        if (!$profile || empty($profile->about_me) || strlen($profile->about_me) < 50) {
            $errors[] = "Giảng viên phải có phần giới thiệu với tối thiểu 50 ký tự.";
        } else {
            $pass[] = "Giảng viên có phần giới thiệu hợp lệ.";
        }

        if (!$profile || empty($profile->phone)) {
            $errors[] = "Giảng viên phải có số điện thoại.";
        } else {
            $pass[] = "Giảng viên có số điện thoại hợp lệ.";
        }

        if (!$profile || empty($profile->address)) {
            $errors[] = "Giảng viên phải có địa chỉ.";
        } else {
            $pass[] = "Giảng viên có địa chỉ.";
        }

        if (!$profile || empty($profile->certificates) || count(json_decode($profile->certificates, true)) < 1) {
            $errors[] = "Giảng viên phải có ít nhất một chứng chỉ.";
        } else {
            $pass[] = "Giảng viên có chứng chỉ.";
        }

        if (!$profile || empty($profile->identity_verification)) {
            $errors[] = "Tài liệu xác minh danh tính không được để trống.";
        } else {
            $pass[] = "Giảng viên có tài liệu xác minh danh tính.";
        }

        if (!$profile || empty($profile->qa_systems) || count(json_decode($profile->qa_systems, true)) < 3) {
            $errors[] = "Giảng viên phải trả lời câu hỏi hệ thống.";
        } else {
            $pass[] = "Giảng viên đã trả lời câu hỏi hệ thống.";
        }

        $progress = $this->calculateProgress($errors, $pass);

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => $pass,
            'progress' => $progress * 100
        ];
    }

    private function calculateProgress(array $errors, array $pass)
    {
        try {
            $countErrors = count($errors);
            $countPass = count($pass);

            return empty($errors) ? 100 : $countPass / ($countErrors + $countPass);
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }
}
