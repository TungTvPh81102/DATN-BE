<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Course;

use App\Models\Document;
use App\Models\Quiz;

use App\Models\User;

use App\Models\Video;
use App\Notifications\CourseModificationNotificationForStudent;
use App\Notifications\CourseModificationRejectedNotification;
use App\Notifications\CourseModificationResponseNotification;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalCourseController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Kiểm duyệt khoá học';
        $subTitle = 'Danh sách khoá học ';

        $queryApprovals = Approvable::query()
            ->with([
                'approver',
                'course.user',
                'user'
            ])
            ->orderBy('id', 'desc')
            ->where('approvable_type', Course::class);

        $approvalCount = Approvable::query()
            ->selectRaw('
                count(id) as total_approval,
                sum(status = "pending") as pending_approval,
                sum(status = "approved") as approved_approval,
                sum(status = "rejected") as rejected_approval
            ')->where('approvable_type', Course::class)->first();

        if ($request->hasAny([
            'amount_min',
            'amount_max',
            'request_start_date',
            'request_end_date',
            'approval_start_date',
            'approval_end_date',
            'course_name_approved',
            'approver_name_approved',
            'instructor_email',
            'name_instructor',
            'status'
        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        if ($request->has('search_full'))
            $queryApprovals = $this->search($request, $queryApprovals);

        $approvals = $queryApprovals->paginate(10);

        if ($request->ajax()) {
            $html = view('approval.course.table', compact('approvals'))->render();
            return response()->json(['html' => $html]);
        }

        return view('approval.course.index', compact([
            'title',
            'subTitle',
            'approvals',
            'approvalCount'
        ]));
    }

    public function show(string $id)
    {
        try {
            $approval = Approvable::query()
                ->where('approvable_type', Course::class)
                ->with([
                    'approver',
                    'approvable.user',
                    'approvable.chapters.lessons.lessonable'
                ])
                ->latest('created_at')
                ->find($id);

            $title = 'Kiểm duyệt khoá học';
            $subTitle = 'Thông tin khoá học: ' . $approval->course->name;

            $totalDuration = $approval->approvable->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            })->filter(function ($lesson) {
                return $lesson->lessonable_type === Video::class;
            })->sum(function ($lesson) {
                return $lesson->lessonable->duration ?? 0;
            });

            $videos = $approval->approvable->chapters
                ->flatMap(fn($chapter) => $chapter->lessons)
                ->filter(fn($lesson) => $lesson->lessonable_type === Video::class)
                ->mapToGroups(fn($lesson) => [$lesson->id => $lesson->lessonable]);
            // dd($videos->toArray());

            $documents = $approval->approvable->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            })->filter(function ($lesson) {
                return $lesson->lessonable_type == Document::class;
            })->mapToGroups(function ($lesson) {
                return [$lesson->id => $lesson->lessonable] ?? null;
            });

            $quizzes = $approval->approvable->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            })->filter(function ($lesson) {
                return $lesson->lessonable_type == Quiz::class;
            })->mapToGroups(function ($lesson) {

                return [$lesson->id => $lesson->lessonable->load('questions.answers')] ?? null;
            });

            return view('approval.course.show', compact([
                'title',
                'subTitle',
                'approval',
                'totalDuration',
                'documents',
                'quizzes',
                'videos',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Khoá học đã được duyệt');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Khoá học đã bị từ chối';
        return $this->updateApprovalStatus($id, 'rejected', $note);
    }

    private function updateApprovalStatus(string $id, string $status, string $note)
    {
        try {
            DB::beginTransaction();

            $approval = Approvable::query()->find($id);

            $approval->status = $status;
            $approval->note = $note;
            $approval->{$status . '_at'} = now();
            $approval->approver_id = auth()->id();
            $approval->save();

            if ($status === 'approved') {
                $approval->course->update([
                    'status' => $status,
                    'accepted' => now()
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            } else {
                $approval->course->update([
                    'status' => $status,
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            }

            DB::commit();

            return redirect()->back()->with('success', "Khoá học đã được $status");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function rejectModifyRequest(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $note = 'Yêu cầu sửa đổi nội dung khóa học bị từ chối';
            $status = 'modify_request_rejected';

            $approval = Approvable::query()->find($id);
            $user = $approval->course->user;
            $approval->status = 'approved';
            $approval->content_modification = false;
            $approval->reason = null;
            $approval->approved_at = now();
            $approval->save();

            $approval->logApprovalAction(
                $status,
                auth()->user(),
                $note
            );

            $approval->course->update([
                'status' => 'approved',
                'modification_request' => false
            ]);

            $user->notify(new CourseModificationResponseNotification(
                $approval->course,
                $status,
                $note
            ));

            DB::commit();

            return redirect()->route('admin.approvals.courses.index')->with('success', "Yêu cầu sửa đổi nội dung khóa học được từ chối");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function approveModifyRequest(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $note = 'Yêu cầu sửa đổi nội dung khóa học được duyệt';
            $status = 'modify_request_approved';

            $approval = Approvable::query()->find($id);
            $user = $approval->course->user;

            $studentIds = DB::table('course_users')
                ->where('course_id', $approval->course->id)
                ->pluck('user_id');

            $students = User::query()->whereIn('id', $studentIds)->get();

            $approval->status = 'modify_request';
            $approval->note = $note;
            $approval->content_modification = false;
            $approval->approver_id = auth()->id();
            $approval->approved_at = now();
            $approval->save();

            $approval->logApprovalAction(
                $status,
                auth()->user(),
                $note
            );

            $approval->course->update([
                'status' => 'draft',
                'modification_request' => true
            ]);

            $user->notify(new CourseModificationResponseNotification(
                $approval->course,
                $status,
                $note
            ));

            foreach ($students as $student) {
                $student->notify(new CourseModificationNotificationForStudent(
                    $approval->course,
                    $student
                ));
            }

            DB::commit();

            return redirect()->route('admin.approvals.courses.index')->with('success', "Yêu cầu sửa đổi nội dung khóa học được đồng ý");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }


    private function filter(Request $request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'course_name_approved' => null,
            'approver_name_approved' => null,
            'course_price_approved' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
        ];

        $email_instructor = $request->input('instructor_email', '');
        $name_instructor = $request->input('name_instructor', '');

        if (!empty($name_instructor) || !empty($email_instructor)) {
            $query->whereHas('course.user', function ($query) use ($email_instructor, $name_instructor) {

                if (!empty($name_instructor)) {
                    $query->where('name', 'LIKE', "%{$name_instructor}%");
                }

                if (!empty($email_instructor)) {
                    $query->where('email', 'LIKE', "%{$email_instructor}%");
                }
            });
        }

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('approver', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('course', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('course.user', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhere('email', 'LIKE', "%$searchTerm%");
                    });
            });
        }

        return $query;
    }
}
