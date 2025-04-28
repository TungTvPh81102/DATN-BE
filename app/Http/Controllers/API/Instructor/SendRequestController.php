<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Jobs\AutoApproveCourseJob;
use App\Mail\CourseSubmitMail;
use App\Models\Approvable;
use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseApprovedNotification;
use App\Notifications\CourseRequestToModifyContentNotification;
use App\Notifications\CourseSubmittedNotification;
use App\Services\CourseValidatorService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRequestController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function submitCourse(Request $request, string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Khóa học không tồn tại');
            }

            if ($course->user_id !== auth()->id()) {
                return $this->respondNotFound('Bạn không có quyền truy cập');
            }

            $errors = CourseValidatorService::validateCourse($course);

            if (!empty($errors)) {
                return $this->respondValidationFailed('Khoá học chưa đạt yêu cầu kiểm duyệt', $errors);
            }

            DB::beginTransaction();

            $status = $course->status;
            $approvable = Approvable::query()->firstOrNew([
                'approvable_id' => $course->id,
                'approvable_type' => Course::class
            ]);

            switch ($status) {
                case 'rejected':
                case 'draft':
                case 'modify_request':
                    $approvable->status = 'pending';
                    $approvable->request_date = now();
                    $approvable->save();
                    $course->update(['status' => 'pending']);

                    break;

                case 'pending':
                    $approvable->delete();
                    $course->update(['status' => 'draft']);
                    break;

                case 'approved':
                    DB::rollBack();
                    return $this->respondError('Khóa học đã được duyệt, không thể gửi yêu cầu');

                default:
                    DB::rollBack();
                    return $this->respondBadRequest('Trạng thái khóa học không hợp lệ');
            }

            if ($status === 'draft' || $status === 'rejected') {
                $managers = User::query()->role(['admin','employee'])->get();

                foreach ($managers as $manager) {
                    $manager->notify(new CourseSubmittedNotification($course));
                }

                AutoApproveCourseJob::dispatch($course)
                    ->delay(now()->addSeconds(10))
                    ->afterCommit();

                DB::commit();
                return $this->respondOk('Gửi yêu cầu thành công');
            } elseif ('pending') {
                $managers = User::query()->role(['admin', 'employee'])->get();

                foreach ($managers as $manager) {
                    $manager->notifications()
                        ->whereJsonContains('data->course_id', $course->id)
                        ->whereJsonContains('data->type', 'register_course')
                        ->delete();
                }

                DB::commit();
                return $this->respondOk('Hủy yêu cầu kiểm duyệt thành công');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function requestToModifyContent(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            $data = $request->validate([
                'slug' => 'required|string|exists:courses,slug',
                'reason' => 'required|string|max:500',
            ]);

            $course = Course::query()->where('slug', $data['slug'])->first();

            if ($course->user_id !== $user->id) {
                return $this->respondForbidden('Bạn không phải chủ sở hữu khóa học');
            }

            if ($course->status !== 'approved') {
                return $this->respondError('Không thể gửi yêu cầu sửa đổi nội dung do khoá học chưa được duyệt');
            }

            $course->update([
                'status' => 'modify_request'
            ]);

            $approvable = Approvable::create([
                'approvable_id' => $course->id,
                'approvable_type' => Course::class,
                'status' => 'pending',
                'request_date' => now(),
                'reason' => $data['reason'],
                'content_modification' => true,
            ]);

            $approvable->save();

            $manager = User::query()
                ->where('email', 'coursemely@gmail.com')
                ->first();

            $manager->notify(new CourseRequestToModifyContentNotification($course));

            DB::commit();

            return $this->respondOk('Gửi yêu cầu sửa đổi nội dung thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function requestApproval($courseId)
    {
        $course = Course::findOrFail($courseId);

        if ($course->status !== 'draft') {
            return response()->json(['message' => 'Khóa học đã gửi yêu cầu hoặc đã được duyệt!'], 400);
        }

        $instructor = User::findOrFail($course->user_id);
        if (!$instructor) {
            return response()->json(['message' => 'Người giảng viên không tồn tại.'], 404);
        }

        $course->status = 'approved';
        $course->save();

        $instructor->notify(new CourseApprovedNotification($course));

        return response()->json(['message' => 'Khóa học đã được duyệt thành công!'], 200);
    }
}
