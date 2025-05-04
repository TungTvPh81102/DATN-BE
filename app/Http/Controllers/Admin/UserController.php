<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateProfileRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\Return_;

class UserController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, FilterTrait;

    const FOLDER = 'users';
    const URLIMAGEDEFAULT = "https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png";


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Danh sách người dùng';
            session()->forget('nameRouteUser');
            $roleUser = $this->getRoleFromSegment();
            $actorRole = $roleUser['role_name'];
            session(['nameRouteUser' => $roleUser]);

            $queryUsers = User::query()->latest('id')->with('profile');

            $queryUserCounts = User::query()
                ->selectRaw('
                    count(id) as total_users,
                    sum(status = "active") as active_users,
                    sum(status = "inactive") as inactive_users,
                    sum(status = "blocked") as blocked_users
                ');

            if ($request->hasAny(['code', 'name', 'email', 'profile_phone_user', 'status', 'created_at', 'updated_at', 'start_deleted', 'end_deleted'])) {
                $queryUsers = $this->filter($request, $queryUsers);
            }

            if ($request->has('search_full')) {
                $queryUsers = $this->search($request->search_full, $queryUsers);
            }

            if ($roleUser['name'] === 'deleted') {
                $queryUsers->with('roles:name')->onlyTrashed();
                $queryUserCounts->onlyTrashed();
            } else {
                $queryUsers->whereHas('roles', function ($query) use ($roleUser) {
                    $query->where('name', $roleUser['name']);
                });

                $queryUserCounts->whereHas('roles', function ($query) use ($roleUser) {
                    $query->where('name', $roleUser['name']);
                });
            }

            $users = $queryUsers->paginate(10);
            $userCounts = $queryUserCounts->first();

            if ($request->ajax()) {
                $html = view('users.table', compact(['users', 'roleUser', 'actorRole']))->render();
                return response()->json(['html' => $html]);
            }

            return view('users.index', compact(['users', 'userCounts', 'subTitle', 'title', 'roleUser', 'actorRole']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Thêm mới người dùng';

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.create', compact([
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('avatar');

            if ($request->hasFile('avatar')) {
                $urlAvatar = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            do {
                $data['code'] = str_replace('-', '', Str::uuid()->toString());
            } while (User::query()->where('code', $data['code'])->exists());

            $data['avatar'] = $urlAvatar ?? self::URLIMAGEDEFAULT;

            $data['email_verified_at'] = now();

            $user = User::query()->create($data);

            $user->assignRole($request->role);

            DB::commit();

            $routeUserByRole = $request->role === 'employee' ? 'employees'
                : ($request->role === 'instructor' ? 'instructors' : 'clients');

            return redirect()->route('admin.' . $routeUserByRole . '.index')->with('success', 'Thêm mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($urlAvatar) && filter_var($urlAvatar, FILTER_VALIDATE_URL)) {
                $this->deleteImage($urlAvatar, self::FOLDER);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user)
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Chi tiết người dùng';

            $user->load(['profile', 'instructorCommissions']);

            $roleUser = 'member';

            if ($user->hasRole('instructor')) {
                $roleUser = 'instructor';
            } elseif ($user->hasRole('employee')) {
                $roleUser = 'employee';
            }

            $courses = $this->getCourseInstructor($user->id);
            $totalStudents = $this->getTotalStudentsByInstructor($user->id);
            $memberships = $this->getMembershipInstructor($user->id);
            $purchases = $this->getHistoryBought($user->id);
            $transactions = $this->getHistoryWithdraw($user->id);
            $totalSpent = $this->getTotaltotalSpent($user->id);
            $totalRevenueInstructor = $this->getTotalRevenueInstructor($user->id);

            if ($roleUser == 'member') {
                $courses = $this->getCourseStudent($user->id);
                $memberships = $this->getMembershipStudent($user->id);
            }

            if ($request->ajax()) {
                $requestType = $request->input('type', 'courses');
                $response = [];

                switch ($requestType) {
                    case 'courses':
                        $response['courses_table'] = view('users.includes.course_table', compact('courses', 'roleUser'))->render();
                        $response['pagination_links_courses'] = $courses->links()->toHtml();
                        break;

                    case 'memberships':
                        $response['memberships_table'] = view('users.includes.membership_table', compact('memberships', 'roleUser'))->render();
                        $response['pagination_links_memberships'] = $memberships->links()->toHtml();
                        break;

                    case 'purchases':
                        $response['purchases_table'] = view('users.includes.purchase_table', compact('purchases'))->render();
                        $response['pagination_links_purchases'] = $purchases->links()->toHtml();
                        break;

                    case 'withdrawals':
                        $response['withdrawals_table'] = view('users.includes.withdrawal_table', compact('transactions'))->render();
                        $response['pagination_links_withdrawals'] = $transactions->links()->toHtml();
                        break;
                }

                return response()->json($response);
            }

            return view('users.show', compact([
                'user',
                'courses',
                'totalStudents',
                'memberships',
                'roleUser',
                'purchases',
                'transactions',
                'totalSpent',
                'totalRevenueInstructor',
                'title',
                'subTitle'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);
            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Cập nhật người dùng';

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.edit', compact([
                'user',
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function profile()
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Cập nhật người dùng';

            $user = Auth::user();

            if (!$user->hasRole('admin')) {
                return abort(403, 'Bạn không có quyền truy cập vào hệ thống.');
            }

            return view('administrator.profile', compact([
                'title',
                'subTitle',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {

            $validator = $request->validated();

            $data = $request->except('avatar','email');

            DB::beginTransaction();

            $currencyAvatar = $user->avatar;
            $currencyRole = $user->roles->pluck('name')->first();

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            $user->update($data);

            if ($request->has('role')) {

                $newRole  = $request->input('role');

                if( $currencyRole !== $newRole )
                {
                    $user->syncRoles([]);
                    $user->assignRole($newRole);
                    Mail::to($user->email)->send(new \App\Mail\UserRoleChangedMail($user, $currencyRole, $newRole));
                }
                $user->syncRoles([]);

                $user->assignRole($request->input('role'));
            }

            if (
                isset($data['avatar']) && !empty($data['avatar'])
                && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                && !empty($currencyAvatar) && $currencyAvatar !== self::URLIMAGEDEFAULT
            ) {
                $this->deleteImage($currencyAvatar, self::FOLDER);
            }

            DB::commit();

            return redirect()->route('admin.users.edit', $user)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['avatar']);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function import(Request $request, string $role = null)
    {
        try {
            $startTime = microtime(true);
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv',
            ], [
                'file.required' => 'Bắt buộc tải file',
                'file.mimes' => 'File tải lên phải thuộc loại xlsx hoặc csv',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }

            $validRoles = Role::pluck('name')->toArray();
            $role = in_array($role, $validRoles) ? $role : 'member';

            $import = new UsersImport($role);
            Excel::import($import, $request->file('file'));

            $endTime = microtime(true);

            Log::info(' Thời gian import: ' . ($endTime - $startTime));

            return redirect()->back()->with('success', 'Import thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    }

    public function export(string $role = null)
    {
        try {

            $validRoles = Role::pluck('name')->toArray();
            $role = in_array($role, $validRoles) ? $role : 'member';

            return Excel::download(new UsersExport($role), 'Users_' . $role . '.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->deleteUsers($userID);
            } else {
                $user = User::query()->findOrFail($id);

                $data['avatar'] = $user->avatar;

                $user->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    public function updateEmailVerified(Request $request, User $user)
    {
        try {
            $data['email_verified_at'] = !empty($request->input('email_verified')) ? now() : null;

            $user->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        } catch (\Exception $e) {

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Cập nhật thất bại'
            ]);
        }
    }

    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->deleteUsers($userID);
            } else {
                $user = User::query()->onlyTrashed()->findOrFail($id);

                $data['avatar'] = $user->avatar;

                $user->forceDelete();

                if (
                    isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                    && $data['avatar'] !== self::URLIMAGEDEFAULT
                ) {
                    $this->deleteImage($data['avatar'], self::FOLDER);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    public function restoreDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->restoreDeleteUsers($userID);
            } else {
                $user = User::query()->onlyTrashed()->findOrFail($id);

                if ($user->trashed()) {
                    $user->restore();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Khôi phục thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Khôi phục thất bại'
            ]);
        }
    }

    private function deleteUsers(array $userID)
    {

        $users = User::query()->whereIn('id', $userID)->withTrashed()->get();

        foreach ($users as $user) {

            $avatar = $user->avatar;

            if ($user->trashed()) {
                $user->forceDelete();

                if (
                    isset($avatar) && !empty($avatar)
                    && filter_var($avatar, FILTER_VALIDATE_URL)
                    && $avatar !== self::URLIMAGEDEFAULT
                ) {
                    $this->deleteImage($avatar, self::FOLDER);
                }
            } else {
                $user->delete();
            }
        }
    }

    private function restoreDeleteUsers(array $userID)
    {

        $users = User::query()->whereIn('id', $userID)->onlyTrashed()->get();

        foreach ($users as $user) {

            $avatar = $user->avatar;

            if ($user->trashed()) {
                $user->restore();
            }
        }
    }

    private function getRoleFromSegment()
    {
        $role = request()->segment(3);

        $role = explode('-', $role)[1];

        $roles = [
            'clients' => ['name' => 'member', 'actor' => 'khách hàng', 'role_name' => 'clients'],
            'instructors' => ['name' => 'instructor', 'actor' => 'giảng viên', 'role_name' => 'instructors'],
            'employees' => ['name' => 'employee', 'actor' => 'nhân viên', 'role_name' => 'employees'],
            'deleted' => ['name' => 'deleted', 'actor' => 'thành viên đã xóa', 'role_name' => 'users.deleted']
        ];

        return $roles[$role] ?? ['name' => 'member', 'actor' => 'khách hàng', 'role_name' => 'clients'];
    }
    private function filter(Request $request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'deleted_at' => ['attribute' => ['start_deleted' => '>=', 'end_deleted' => '<=']],
            'code' => ['queryWhere' => 'LIKE'],
            'name' => ['queryWhere' => 'LIKE'],
            'email' => ['queryWhere' => 'LIKE'],
            'status' => ['queryWhere' => '='],
            'profile_phone_user' => null,
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('email', 'LIKE', "%$searchTerm%")
                    ->orWhere('code', 'LIKE', "%$searchTerm%")
                    ->orWhereHas('profile', function ($query) use ($searchTerm) {
                        $query->where('phone', 'LIKE', "%$searchTerm%");
                    });
            });
        }

        return $query;
    }
    public function profileUpdate(UpdateProfileRequest $request, User $user)
    {
        try {

            $validator = $request->validated();

            $data = $request->except('avatar');

            DB::beginTransaction();

            $currencyAvatar = $user->avatar;

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            if ($request->filled('current_password') && $request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            $data['email'] = $user->email;

            $data['status'] = $user->status;

            $user->update($data);

            if (
                isset($data['avatar']) && !empty($data['avatar'])
                && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                && !empty($currencyAvatar) && $currencyAvatar !== self::URLIMAGEDEFAULT
            ) {
                $this->deleteImage($currencyAvatar, self::FOLDER);
            }

            DB::commit();

            return redirect()->route('admin.administrator.profile', $user)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['avatar']);
            }

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    private function getCourseInstructor($instructorId)
    {
        $courses = DB::table('courses')
            ->select(
                'courses.id',
                'courses.name',
                'courses.slug',
                'courses.thumbnail',
            )->where(['courses.status' => 'approved', 'courses.user_id' => $instructorId]);

        $invoices = DB::table('invoices')
            ->select('invoices.course_id', DB::raw('SUM(invoices.final_amount*invoices.instructor_commissions) as total_revenue'))
            ->where('invoices.status', 'Đã thanh toán')
            ->groupBy('invoices.course_id');

        $invoices =  DB::table(DB::raw("({$invoices->toSql()}) as invoices"))->mergeBindings($invoices)
            ->joinSub($courses, 'courses', function ($join) {
                $join->on('courses.id', '=', 'invoices.course_id');
            });

        $ratings = DB::table('ratings')
            ->select(DB::raw('AVG(DISTINCT ratings.rate) as avg_rating'), 'ratings.course_id')
            ->joinSub($courses, 'courses', function ($join) {
                $join->on('ratings.course_id', '=', 'courses.id');
            })->groupBy('ratings.course_id');

        $course_users = DB::table('course_users')
            ->select('course_users.course_id', DB::raw('COUNT(course_users.user_id) as total_student'), DB::raw('AVG(course_users.progress_percent) as avg_progress'))
            ->joinSub($courses, 'courses', function ($join) {
                $join->on('courses.id', '=', 'course_users.course_id');
            })->groupBy('course_users.course_id');

        $courseRevenue = DB::table(DB::raw("({$courses->toSql()}) as courses"))
            ->mergeBindings($courses)
            ->select(
                'courses.id',
                'courses.name',
                'courses.slug',
                'courses.thumbnail',
                'course_users.total_student',
                DB::raw('ROUND(COALESCE(invoices.total_revenue), 2) as total_revenue'),
                DB::raw('ROUND(COALESCE(course_users.avg_progress),2) as avg_progress'),
                DB::raw('ROUND(COALESCE(ratings.avg_rating), 1) as avg_rating')
            )
            ->joinSub($invoices, 'invoices', function ($join) {
                $join->on('invoices.course_id', '=', 'courses.id');
            })
            ->leftJoinSub($ratings, 'ratings', function ($join) {
                $join->on('ratings.course_id', '=', 'courses.id');
            })
            ->leftJoinSub($course_users, 'course_users', function ($join) {
                $join->on('course_users.course_id', '=', 'courses.id');
            })
            ->orderByDesc('total_revenue')
            ->paginate(5);

        return $courseRevenue;
    }
    private function getCourseStudent($userId)
    {
        $invoices = DB::table('invoices')
            ->select('invoices.course_id', 'invoices.created_at')
            ->where(['invoices.status' => 'Đã thanh toán', 'invoices.user_id' => $userId])
            ->where('invoices.course_id', '!=', Null);

        $invoices = DB::table('courses')
            ->joinSub($invoices, 'invoices', function ($join) {
                $join->on('courses.id', '=', 'invoices.course_id');
            })
            ->select('invoices.*', 'courses.id', 'courses.name', 'courses.slug', 'courses.thumbnail');

        $course_users = DB::table('course_users')
            ->select('course_users.course_id', 'course_users.progress_percent')
            ->where('user_id', $userId);

        $courseRevenue = DB::table(DB::raw("({$invoices->toSql()}) as invoices"))
            ->mergeBindings($invoices)
            ->select(
                'invoices.id',
                'invoices.name',
                'invoices.slug',
                'invoices.thumbnail',
                'course_users.progress_percent',
                'invoices.created_at'
            )->leftJoinSub($course_users, 'course_users', function ($join) {
                $join->on('course_users.course_id', '=', 'invoices.course_id');
            })
            ->orderByDesc('invoices.created_at')
            ->paginate(5);

        return $courseRevenue;
    }
    private function getTotalStudentsByInstructor($instructorId)
    {
        return DB::table('courses')
            ->join('course_users', function ($join) {
                $join->on('courses.id', '=', 'course_users.course_id');
            })
            ->where('courses.user_id', $instructorId)
            ->distinct('course_users.user_id')
            ->count('course_users.user_id');
    }
    private function getMembershipStudent($userId)
    {
        $invoices = DB::table('invoices')
            ->select('invoices.membership_plan_id', 'invoices.created_at')
            ->where(['invoices.status' => 'Đã thanh toán', 'invoices.user_id' => $userId])
            ->where('invoices.membership_plan_id', '!=', Null);

        $invoices = DB::table('membership_plans')
            ->joinSub($invoices, 'invoices', function ($join) {
                $join->on('membership_plans.id', '=', 'invoices.membership_plan_id');
            })
            ->select('invoices.*', 'membership_plans.id', 'membership_plans.name', 'membership_plans.duration_months');
        $membershipRevenue = DB::table(DB::raw("({$invoices->toSql()}) as invoices"))
            ->mergeBindings($invoices)
            ->select(
                'invoices.id',
                'invoices.name',
                'invoices.duration_months',
                'invoices.created_at'
            )
            ->orderByDesc('invoices.created_at')
            ->paginate(5);

        return $membershipRevenue;
    }
    private function getMembershipInstructor($instructorId)
    {
        $membership = DB::table('membership_plans')
            ->select(
                'membership_plans.id',
                'membership_plans.name',
                'membership_plans.duration_months',
                'membership_plans.created_at'
            )->where(['membership_plans.status' => 'active', 'membership_plans.instructor_id' => $instructorId]);

        $invoices = DB::table('invoices')
            ->select(
                'invoices.membership_plan_id',
                DB::raw('SUM(invoices.final_amount*invoices.instructor_commissions) as total_revenue, COUNT(DISTINCT user_id) as total_bought')
            )->where('invoices.status', 'Đã thanh toán')
            ->groupBy('invoices.membership_plan_id');

        $invoices =  DB::table(DB::raw("({$invoices->toSql()}) as invoices"))->mergeBindings($invoices)
            ->joinSub($membership, 'membership_plans', function ($join) {
                $join->on('membership_plans.id', '=', 'invoices.membership_plan_id');
            });

        $membershipRevenue = DB::table(DB::raw("({$membership->toSql()}) as membership_plans"))
            ->mergeBindings($membership)
            ->select(
                'membership_plans.id',
                'membership_plans.name',
                'membership_plans.duration_months',
                DB::raw('ROUND(COALESCE(invoices.total_revenue), 2) as total_revenue'),
                DB::raw('COALESCE(invoices.total_bought) as total_bought'),
                'membership_plans.created_at'
            )
            ->joinSub($invoices, 'invoices', function ($join) {
                $join->on('membership_plans.id', '=', 'invoices.membership_plan_id');
            })
            ->orderByDesc('total_revenue')
            ->paginate(5);

        return $membershipRevenue;
    }
    private function getHistoryBought($userId)
    {
        return Invoice::query()
            ->where(['user_id' => $userId, 'status' => 'Đã thanh toán'])
            ->with(['course', 'membershipPlan'])
            ->paginate(5);
    }
    private function getHistoryWithdraw($userID)
    {
        return Transaction::query()
            ->where(['user_id' => $userID, 'status' => 'Giao dịch thành công', 'type' => 'withdrawal'])
            ->with('transactionable')
            ->paginate(5);
    }
    private function getTotaltotalSpent($userId)
    {
        return DB::table('invoices')
            ->select(DB::raw('SUM(final_amount) as totalSpent'))
            ->where(['user_id' => $userId, 'status' => 'Đã thanh toán'])
            ->first();
    }
    private function getTotalRevenueInstructor($instructorId){
        return Invoice::query()
        ->select([
            DB::raw('SUM(final_amount*instructor_commissions) as total_revenue'),
            DB::raw('SUM(final_amount*(1-instructor_commissions)) as total_instructor_share')
        ])
        ->whereHas('course', function($query) use ($instructorId){
            $query->where('user_id', $instructorId);
        })
        ->where('status', 'Đã thanh toán')
        ->first();
    }
}
