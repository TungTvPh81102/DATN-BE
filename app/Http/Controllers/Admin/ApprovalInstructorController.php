<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ApprovalsIntructorExport;
use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\User;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ApprovalInstructorController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Kiểm duyệt giảng viên';
            $subTitle = 'Danh sách giảng viên ';

            $queryApprovals = Approvable::query()
                ->with([
                    'approver',
                    'user.profile',
                ])
                ->orderBy('id', 'desc')
                ->where('approvable_type', User::class);

            $approvalCount = Approvable::query()
                ->selectRaw('
                    count(id) as total_approval,
                    sum(status = "pending") as pending_approval,
                    sum(status = "approved") as approved_approval,
                    sum(status = "rejected") as rejected_approval
                ')->where('approvable_type', User::class)->first();

            if ($request->hasAny([
                'request_start_date',
                'request_end_date',
                'approval_start_date',
                'approval_end_date',
                'approver_name_approved',
                'instructor_email',
                'name_instructor',
                'phone_instructor',
                'status'
            ])) {
                $queryApprovals = $this->filter($request, $queryApprovals);
            }

            if ($request->has('search_full'))
                $queryApprovals = $this->search($request, $queryApprovals);

            $approvals = $queryApprovals->paginate(10);

            if ($request->ajax()) {
                $html = view('approval.instructor.table', compact('approvals'))->render();
                return response()->json(['html' => $html]);
            }

            return view('approval.instructor.index', compact([
                'title',
                'subTitle',
                'approvals',
                'approvalCount'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->route('admin.approvals.instructor.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $id)
    {
        try {
            $approval = Approvable::query()
                ->with([
                    'approver',
                    'user.profile.careers',
                    'user.followers',
                    'user.following', 
                ])
                ->withCount([
                    'user as follower_count' => function ($query) {
                        $query->whereHas('followers');
                    },
                    'user as following_count' => function ($query) {
                        $query->whereHas('following');
                    },
                ])
                ->where('id', $id)->first();

            $score = $this->calculateCompletenessScore($approval->user);

            $title = 'Kiểm duyệt giảng viên';
            $subTitle = 'Thông tin giảng viên: ' . $approval->user->name;

            return view('approval.instructor.show', compact([
                'title',
                'subTitle',
                'approval',
                'score',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->route('admin.approvals.instructors.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function calculateCompletenessScore($user)
    {
        $score = 0;
        $criteriaCount = 0;
        $personalInfoRequired = ['phone', 'address', 'bio', 'about_me'];
        $missingInfoCount = 0;

        if (empty($user->name) || empty($user->email) || empty($user->avatar)) {
            $missingInfoCount++;
        }

        foreach ($personalInfoRequired as $info) {
            if (empty($user->profile->$info)) {
                $missingInfoCount++;
            }
        }

        if ($missingInfoCount == 0) {
            $score += 60;
        } else {
            $score += 60 - (10 * $missingInfoCount);
        }
        $criteriaCount++;

        $qa_systems = $user->profile->qa_systems ? json_decode($user->profile->qa_systems, true) : [];
        if (count($qa_systems) > 0) {
            $score += 10;
            $criteriaCount++;
        }

        $qualifications = $user->profile->careers ?? [];
        if (count($qualifications) > 0) {
            $score += 10;
            $criteriaCount++;
        }

        $certificates = $user->profile->certificates ? json_decode($user->profile->certificates, true) : [];
        if (count($certificates) > 0) {
            $score += 10;
            $criteriaCount++;
        }

        if (($user->profile->experience ?? 0) >= 1) {
            $score += 5;
            $criteriaCount++;
        }

        if (!empty($user->profile->bio)) {
            $score += 5;
            $criteriaCount++;
        }

        if ($criteriaCount > 0) {
            $score = round($score, 2);
        }

        return $score;
    }

    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Giảng viên đã được kiểm duyệt', 'instructor');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Giảng viên đã bị từ chối';
        return $this->updateApprovalStatus($id, 'rejected', $note, 'member');
    }

    private function updateApprovalStatus(string $id, string $status, string $note, string $newRole)
    {
        try {
            DB::beginTransaction();

            $approval = Approvable::query()->findOrFail($id);
            $user = $approval->user;

            $approval->status = $status;
            $approval->note = $note;
            $approval->{$status . '_at'} = now();
            $approval->approver_id = auth()->id();
            $approval->logApprovalAction($status, auth()->user(), $note);
            $approval->save();

            $user->syncRoles([$newRole]);

            DB::commit();

            return redirect()->back()->with('success', "Giảng viên đã được $status");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e);

            return redirect()->route('admin.approvals.instructors.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'approver_name_approved' => null,
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        $email_instructor = $request->input('instructor_email', '');
        $name_instructor = $request->input('name_instructor', '');
        $phone_instructor = $request->input('phone_instructor', '');

        if (!empty($name_instructor) || !empty($email_instructor) || !empty($phone_instructor)) {
            $query->whereHas('user', function ($query) use ($email_instructor, $name_instructor, $phone_instructor) {

                if (!empty($name_instructor)) {
                    $query->where('name', 'LIKE', "%{$name_instructor}%");
                }

                if (!empty($email_instructor)) {
                    $query->where('email', 'LIKE', "%{$email_instructor}%");
                }

                if (!empty($phone_instructor)) {
                    $query->whereHas('profile', function ($query) use ($phone_instructor) {
                        $query->where('phone', 'LIKE', "%$phone_instructor%");
                    });
                }
            });
        }

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
                    ->orWhereHas('user', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhere('email', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('profile', function ($query) use ($searchTerm) {
                                $query->where('phone', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }

    public function export()
    {
        try {

            return Excel::download(new ApprovalsIntructorExport, 'danh_sach_kiem_duyet_giang_vien.xlsx');

        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
