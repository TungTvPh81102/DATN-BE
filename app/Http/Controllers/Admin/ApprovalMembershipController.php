<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Notifications\MembershipApprovalNotification;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalMembershipController extends Controller
{
    //
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Kiểm duyệt Membership';
        $subTitle = 'Danh sách Membership cần duyệt';

        // Lấy danh sách các Membership Plans cần kiểm duyệt
        $queryApprovals = Approvable::query()
            ->with([
                'approver',
                'membershipPlan.instructor.profile',
            ])
            ->orderBy('id', 'desc')
            ->where('approvable_type', MembershipPlan::class)
            ->orderBy('id', 'desc');


        // Thống kê số lượng kiểm duyệt theo trạng thái
        $approvalCount = Approvable::query()
            ->selectRaw('
            count(id) as total_approval,
            sum(status = "pending") as pending_approval,
            sum(status = "approved") as approved_approval,
            sum(status = "rejected") as rejected_approval
        ')->where('approvable_type', MembershipPlan::class)->first();

        if ($request->has('search_full')) {
            $queryApprovals = $this->search($request, $queryApprovals);
        }

        if ($request->hasAny([
            'request_start_date',
            'request_end_date',
            'approval_start_date',
            'approval_end_date',
            'status',
            'approver_name_approved',
            'membershipPlan_name_approved',
            'amount_min',
            'amount_max',
            'instructor_email',
            'name_instructor',
            'phone_instructor',
            'membershipPlan_duration_months_approval',
        ])) {
            $queryApprovals = $this->filter($request, $queryApprovals);
        }

        $approvals = $queryApprovals->paginate(10);

        if ($request->ajax()) {
            $html = view('approval.membership.table', compact('approvals'))->render();
            return response()->json(['html' => $html]);
        }

        return view('approval.membership.index', compact([
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
                ->where('approvable_type', MembershipPlan::class)
                ->with([
                    'approver',
                    'membershipPlan.instructor',
                ])
                ->latest('created_at')
                ->find($id);

            $courses = null;

            if ($approval && $approval->membershipPlan) {
                $courses = $approval->membershipPlan->membershipCourseAccess()
                    ->paginate(5);
            }

            $conditionalMembership = $this->conditionalMembership($approval->membershipPlan);

            $title = 'Kiểm duyệt gói thành viên';
            $subTitle = 'Thông tin kiểm duyệt';

            return view('approval.membership.show', compact([
                'title',
                'subTitle',
                'approval',
                'conditionalMembership',
                'courses',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getCourses(string $id, Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $approval = Approvable::query()
                ->where('approvable_type', MembershipPlan::class)
                ->with(['membershipPlan'])
                ->find($id);

            if (!$approval) {
                return response()->json(['error' => 'Approval not found'], 404);
            }

            $courses = $approval->membershipPlan->membershipCourseAccess()
                ->paginate(5);

            return view('approval.membership.courses-table', compact('courses'));
        } catch (\Exception $e) {
            $this->logError($e);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function approve(Request $request, string $id)
    {
        return $this->updateApprovalStatus($id, 'approved', 'Gói thành viên đã được duyệt');
    }

    public function reject(Request $request, string $id)
    {
        $note = $request->note ?? 'Gói thành viên đã bị từ chối';
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

            $instructor = $approval->membershipPlan->instructor;

            if ($status === 'approved') {
                $approval->membershipPlan->update([
                    'status' => 'active',
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            } else {
                $approval->membershipPlan->update([
                    'status' => 'draft',
                ]);

                $approval->logApprovalAction(
                    $status,
                    auth()->user(),
                    $note
                );
            }

            $instructor->notify(new MembershipApprovalNotification($status, $note,  $approval->membershipPlan));

            DB::commit();

            return redirect()->back()->with('success', "Gói thành viên đã được $status");
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->route('admin.approvals.courses.index')
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    private function conditionalMembership($memberShipPlan)
    {
        $errors = [];
        $pass = [];
        $courseCount = $memberShipPlan->membershipCourseAccess->count();

        if (empty($memberShipPlan->name) || strlen($memberShipPlan->name) < 5) {
            $errors[] = "Gói thành viên phải có tên với tối thiểu 5 ký tự.";
        }else{
            $pass[] = "Gói thành viên phải có tên với tối thiểu 5 ký tự.";
        }

        if (empty($memberShipPlan->description) || strlen($memberShipPlan->description) < 10) {
            $errors[] = "Mô tả gói thành viên phải có tối thiểu 10 ký tự.";
        }else{
            $pass[] = "Mô tả gói thành viên phải có tối thiểu 10 ký tự.";
        }

        if ($courseCount < 5) {
            $errors[] = 'Gói phải có tối thiểu 5 khoá học để có thể gửi yêu cầu';
        }else{
            $pass[] = "Gói phải có tối thiểu 5 khoá học để có thể gửi yêu cầu";
        }

        return ['errors' => $errors,'pass' => $pass];
    }
    private function filter(Request $request, $query)
    {
        $filters = [
            'request_date' => ['attribute' => ['request_start_date' => '>=', 'request_end_date' => '<=']],
            'approval_date' => ['filed' => ['approved_at', 'rejected_at'], 'attribute' => ['approval_start_date' => '>=', 'approval_end_date' => '<=']],
            'status' => ['queryWhere' => '='],
            'approver_name_approved' => null,
            'membershipPlan_name_approved' => null,
            'membershipPlan_price_approval' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        $email_instructor = $request->input('instructor_email', '');
        $name_instructor = $request->input('name_instructor', '');
        $phone_instructor = $request->input('phone_instructor', '');
        $duration_month = $request->input('membershipPlan_duration_months_approval', '');

        if (!empty($name_instructor) || !empty($email_instructor) || !empty($phone_instructor)) {
            $query->whereHas('membershipPlan.instructor', function ($query) use ($email_instructor, $name_instructor) {

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

        if (!empty($duration_month)) {
            $query->whereHas('membershipPlan', function ($query) use ($duration_month) {
                $query->where('duration_months', $duration_month);
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
                    ->orWhereHas('membershipPlan', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%$searchTerm%");
                    })
                    ->orWhereHas('membershipPlan.instructor', function ($query) use ($searchTerm) {
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
}
