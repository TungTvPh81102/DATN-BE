<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DashBoardExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{

    use LoggableTrait;
    const RATE = 0.4;
    public function index(Request $request)
    {
        try {
            $title = 'Dashboard';
            $year = now()->year;

            $queryTotalAmount = $this->getTotalAmount();
            $queryTotalCourse = $this->getTotalCourse();
            $queryTotalInstructor = $this->getTotalInstructor();
            $queryTopInstructors = $this->getTopInstructor($request);
            $queryTopUsers = $this->getTopUser();
            $queryTopCourses = $this->getTopCourse();
            $querySystem_Funds = $this->getSystemFund();
            $queryCourseRatings = $this->getCourseRating();
            $queryTopCoursesProgress = $this->getTopCourseProgress();
            $queryGetTopViewCourses = $this->getTopViewCourse();
            $quertTopInstructorsFollows = $this->getTopInstructorFollow();
            $queryCategoryStats = $this->getCategoryStat();
            $queryTotalByPaymentMethodAndInvoiceType = $this->getTotalByPaymentMethodAndInvoiceType();

            $queryGetTopViewCourses = $this->filterTopCourseView($queryGetTopViewCourses, $request);

            list(
                $queryTopUsers,
                $queryTopCourses,
                $queryCourseRatings,
                $querySystem_Funds,
                $queryTotalAmount,
                $queryTotalCourse,
                $queryTotalInstructor,
                $queryTopCoursesProgress,
                $queryGetTopViewCourses,
                $quertTopInstructorsFollows,
                $queryCategoryStats,
                $queryTotalByPaymentMethodAndInvoiceType
            ) = $this->getFilterDataChart(
                $request,
                $queryTopUsers,
                $queryTopCourses,
                $queryCourseRatings,
                $querySystem_Funds,
                $queryTotalAmount,
                $queryTotalCourse,
                $queryTotalInstructor,
                $queryTopCoursesProgress,
                $queryGetTopViewCourses,
                $quertTopInstructorsFollows,
                $queryCategoryStats,
                $queryTotalByPaymentMethodAndInvoiceType
            );

            $topInstructors = DB::table(DB::raw("({$queryTopInstructors->toSql()}) as sub"))
                ->mergeBindings($queryTopInstructors)
                ->paginate(5);

            $topUsers = DB::table(DB::raw("({$queryTopUsers->toSql()}) as sub"))
                ->mergeBindings($queryTopUsers)
                ->paginate(5);

            $topCourses = DB::table(DB::raw("({$queryTopCourses->toSql()}) as sub"))
                ->mergeBindings($queryTopCourses)
                ->paginate(5);

            $totalAmount = $queryTotalAmount->first();
            $totalCourse = $queryTotalCourse->count();
            $totalInstructor = $queryTotalInstructor->count();
            $courseRatings = $queryCourseRatings->get();
            $system_Funds = $querySystem_Funds->get();
            $topCoursesProgress = $queryTopCoursesProgress->get();
            $getTopViewCourses = $queryGetTopViewCourses->get();
            $topInstructorsFollows = $quertTopInstructorsFollows->get();
            $categoryStats = $queryCategoryStats->get();
            $totalByPaymentMethodAndInvoiceType = $queryTotalByPaymentMethodAndInvoiceType->first();

            if ($request->ajax()) {
                return response()->json([
                    'top_courses_table' => view('revenue-statistics.includes.top_courses', compact('topCourses'))->render(),
                    'top_instructors_table' => view('revenue-statistics.includes.top_instructors', compact('topInstructors'))->render(),
                    'top_users_table' => view('revenue-statistics.includes.top_users', compact('topUsers'))->render(),
                    'getTopViewCourses' => view('revenue-statistics.includes.top_views', compact('getTopViewCourses'))->render(),
                    'pagination_links_courses' => $topCourses->links()->toHtml(),
                    'pagination_links_instructors' => $topInstructors->links()->toHtml(),
                    'pagination_links_users' => $topUsers->links()->toHtml(),
                    'system_Funds' => $system_Funds,
                    'course_rating' => $courseRatings,
                    'topCourses' => $topCourses,
                    'topInstructors' => $topInstructors,
                    'topUsers' => $topUsers,
                    'totalAmount' => $totalAmount,
                    'totalCourse' => $totalCourse,
                    'totalInstructor' => $totalInstructor,
                    'categoryStats' => $categoryStats,
                    'topCoursesProgress' => $topCoursesProgress,
                    'topInstructorsFollows' => $topInstructorsFollows,
                    'totalByPaymentMethodAndInvoiceType' => $totalByPaymentMethodAndInvoiceType
                ]);
            }

            return view('dashboard', compact([
                'title',
                'totalAmount',
                'totalCourse',
                'totalInstructor',
                'topInstructors',
                'topCourses',
                'topUsers',
                'system_Funds',
                'courseRatings',
                'topCoursesProgress',
                'getTopViewCourses',
                'topInstructorsFollows',
                'categoryStats',
                'totalByPaymentMethodAndInvoiceType'
            ]));
        } catch (\Throwable $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Lấy dữ liệu không thành công vui lòng thử lại');
        }
    }

    private function applyGlobalFilter($query, Request $request, $table, $column)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $year = now()->year;

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween("{$table}.{$column}", [$startDate, $endDate]);
        } else {
            $query->where("{$table}.{$column}", '>=', "{$year}-01-01 00:00:00")
                ->where("{$table}.{$column}", '<', now()->startOfDay());
        }

        return $query;
    }

    private function filterTopCourseView($query, Request $request)
    {
        $orderby_course = $request->input('orderby_course', 'views');

        switch ($orderby_course) {
            case 'created_at':
                $query->orderBy('courses.created_at', 'desc');
                break;

            case 'price_asc':
                $query->orderBy('courses.price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('courses.price', 'desc');
                break;
            default:
                $query->orderBy('courses.views', 'desc');
                break;
        }

        return $query;
    }

    private function getFilterDataChart(
        Request $request,
        $queryTopUsers,
        $queryTopCourses,
        $queryCourseRatings,
        $querySystem_Funds,
        $queryTotalAmount,
        $queryTotalCourse,
        $queryTotalInstructor,
        $queryTopCoursesProgress,
        $queryGetTopViewCourses,
        $quertTopInstructorsFollows,
        $queryCategoryStats,
        $queryTotalByPaymentMethodAndInvoiceType
    ) {
        $queryTopUsers = $this->applyGlobalFilter($queryTopUsers, $request, 'invoices', 'created_at');
        $queryTopCourses = $this->applyGlobalFilter($queryTopCourses, $request, 'invoices', 'created_at');
        $queryCourseRatings = $this->applyGlobalFilter($queryCourseRatings, $request, 'courses', 'created_at');
        $querySystem_Funds = $this->applyGlobalFilter($querySystem_Funds, $request, 'invoices', 'created_at');
        $queryTotalAmount = $this->applyGlobalFilter($queryTotalAmount, $request, 'invoices', 'created_at');
        $queryTotalCourse = $this->applyGlobalFilter($queryTotalCourse, $request, 'courses', 'created_at');
        $queryTotalInstructor = $this->applyGlobalFilter($queryTotalInstructor, $request, 'users', 'created_at');
        $queryTopCoursesProgress = $this->applyGlobalFilter($queryTopCoursesProgress, $request, 'course_users', 'created_at');
        $queryGetTopViewCourses = $this->applyGlobalFilter($queryGetTopViewCourses, $request, 'courses', 'created_at');
        $quertTopInstructorsFollows = $this->applyGlobalFilter($quertTopInstructorsFollows, $request, 'users', 'created_at');
        $queryCategoryStats = $this->applyGlobalFilter($queryCategoryStats, $request, 'categories', 'created_at');
        $queryTotalByPaymentMethodAndInvoiceType = $this->applyGlobalFilter($queryTotalByPaymentMethodAndInvoiceType, $request, 'invoices', 'created_at');

        return [
            $queryTopUsers,
            $queryTopCourses,
            $queryCourseRatings,
            $querySystem_Funds,
            $queryTotalAmount,
            $queryTotalCourse,
            $queryTotalInstructor,
            $queryTopCoursesProgress,
            $queryGetTopViewCourses,
            $quertTopInstructorsFollows,
            $queryCategoryStats,
            $queryTotalByPaymentMethodAndInvoiceType
        ];
    }

    public function export(Request $request)
    {
        try {
            $type = $request->input('type');
            $data = $request->input('data');
            $formattedData = [];

            if (!$data) {
                return;
            }

            switch ($type) {
                case 'top_instructor':
                    $headings = ['Người hướng dẫn', 'Khóa học đã bán', 'Học viên', 'Doanh thu'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_courses'], $item['total_enrolled_students'], $item['total_revenue'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_courses'] ?? 0),
                            number_format($item['total_enrolled_students'] ?? 0),
                            number_format($item['total_revenue'] ?? 0)
                        ];
                    }, $data);
                    break;

                case 'top_course':
                    $headings = ['Khóa học', 'Đã bán', 'Người học', 'Doanh thu'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_sales'], $item['total_enrolled_students'], $item['total_revenue'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_sales'] ?? 0),
                            number_format($item['total_enrolled_students'] ?? 0),
                            number_format($item['total_revenue'] ?? 0)
                        ];
                    }, $data);
                    break;

                case 'top_student':
                    $headings = ['Học viên', 'Khóa học đã mua', 'Tổng tiền đã chi', 'Lần mua gần nhất'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['name'], $item['total_courses_purchased'], $item['total_spent'], $item['last_purchase_date'])) {
                            return;
                        }
                        return [
                            $item['name'],
                            number_format($item['total_courses_purchased'] ?? 0),
                            number_format($item['total_spent'] ?? 0),
                            $item['last_purchase_date'] ?? ''
                        ];
                    }, $data);
                    break;

                case 'top_category':
                    $headings = ['Danh mục', 'Khóa học', 'Học viên', 'Giảng viên'];
                    $formattedData = array_map(function ($item) {
                        if (!isset($item['category_name'], $item['total_courses'], $item['total_enrolled_students'], $item['total_instructors'])) {
                            return;
                        }
                        return [
                            $item['category_name'],
                            number_format($item['total_courses'] ?? 0),
                            number_format($item['total_enrolled_students'] ?? 0),
                            number_format($item['total_instructors'] ?? 0)
                        ];
                    }, $data);
                    break;

                default:
                    return;
            }

            if (empty($formattedData)) {
                $formattedData[] = ['Không có dữ liệu', '', '', ''];
            }

            return Excel::download(new DashBoardExport($formattedData, $headings), $type . '_export.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return;
        }
    }

    private function getTotalCourse()
    {
        return Course::query()
            ->where('status', 'approved')->pluck('id');
    }

    private function getTotalInstructor()
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })->pluck('id');
    }

    private function getTotalAmount()
    {
        return DB::table('invoices')
            ->selectRaw('SUM(final_amount) as total_revenue, SUM(final_amount * ?) as total_profit', [self::RATE])
            ->where('status', 'Đã thanh toán');
    }

    private function getCategoryStat()
    {
        return DB::table('categories')
            ->select(
                'categories.id',
                'categories.name as category_name',
                DB::raw('COALESCE(COUNT(*), 0) as total_courses'),
                DB::raw('COALESCE(COUNT(DISTINCT courses.total_student), 0) as total_enrolled_students'),
                DB::raw('COALESCE(COUNT(DISTINCT courses.user_id), 0) as total_instructors')
            )
            ->leftJoinSub(
                DB::table('courses')
                    ->select('id', 'category_id', 'user_id', 'total_student')
                    ->where('status', 'approved'),
                'courses',
                function ($join) {
                    $join->on('courses.category_id', '=', 'categories.id');
                }
            )
            ->groupBy('categories.id', 'categories.name')
            ->limit(10);
    }

    private function getTopInstructorFollow()
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.avatar', 'users.code', 'users.created_at')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'instructor');
            })
            ->where('users.status', '!=', 'blocked');

        return DB::table(DB::raw("({$users->toSql()}) as users"))
            ->mergeBindings($users)
            ->leftJoinSub(
                DB::table('follows')
                    ->select('instructor_id', DB::raw('COUNT(id) as total_follow'))
                    ->groupBy('instructor_id'),
                'follows',
                'follows.instructor_id',
                '=',
                'users.id'
            )
            ->leftJoinSub(
                DB::table('courses')
                    ->select('user_id', DB::raw('SUM(total_student) as total_student'))
                    ->groupBy('user_id'),
                'courses',
                'courses.user_id',
                '=',
                'users.id'
            )
            ->select(
                'users.id',
                'users.name',
                'users.code',
                'users.avatar',
                DB::raw('COALESCE(follows.total_follow, 0) as total_follow'),
                DB::raw('COALESCE(courses.total_student, 0) as total_student')
            )
            ->orderByDesc('total_follow')
            ->limit(10);
    }

    private function getTopViewCourse()
    {
        return DB::table('courses')
            ->select('courses.name', 'users.name as instructor_name', 'courses.thumbnail', 'courses.views', 'courses.price', 'courses.price_sale', 'courses.is_free', 'courses.slug', 'courses.id')
            ->leftJoinSub(
                DB::table('users')
                    ->select('id', 'name'),
                'users',
                function ($join) {
                    $join->on('users.id', '=', 'courses.user_id');
                }
            )
            ->limit(10);
    }

    private function getTopCourseProgress()
    {
        return CourseUser::selectRaw('course_id, ROUND(AVG(progress_percent),2) as avg_progress')
            ->groupBy('course_id')
            ->orderByDesc('avg_progress')
            ->with('course:id,name')
            ->limit(10);
    }

    private function getTopInstructor(Request $request)
    {
        $query = DB::table('courses')
            ->select(
                'courses.user_id',
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('ROUND(SUM(invoices.final_amount) * 0.6, 0) as total_revenue'),
            )
            ->leftJoin('invoices', function ($join) {
                $join->on('courses.id', '=', 'invoices.course_id')
                    ->where('invoices.status', 'Đã thanh toán');
            })
            ->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'courses.user_id')
                    ->where('users.status', '!=', 'blocked');
            });

        $query = $this->applyGlobalFilter($query, $request, 'invoices', 'created_at');

        $totalRevenue = $query
            ->groupBy('courses.user_id', 'users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('total_revenue')
            ->limit(10);

        $id_topCourse = $totalRevenue->pluck('user_id');

        $totalCourses = DB::table('courses')
            ->select('user_id', DB::raw('COUNT(id) as total_courses'))
            ->whereIn('user_id', $id_topCourse)
            ->groupBy('user_id');

        $totalStudents = DB::table('courses')
            ->select('courses.user_id', DB::raw('COALESCE(COUNT(DISTINCT course_users.user_id), 0) as total_enrolled_students'))
            ->leftJoin('course_users', function ($join) {
                $join->on('courses.id', '=', 'course_users.course_id')
                    ->where(['course_users.source' => 'purchase', 'course_users.access_status' => 'active']);
            })
            ->whereIn('courses.user_id', $id_topCourse)
            ->groupBy('courses.user_id');

        return DB::table(DB::raw("({$totalRevenue->toSql()}) as revenue"))
            ->mergeBindings($totalRevenue)
            ->leftJoinSub($totalCourses, 'courses', 'courses.user_id', '=', 'revenue.user_id')
            ->leftJoinSub($totalStudents, 'students', 'students.user_id', '=', 'revenue.user_id')
            ->select(
                'revenue.id',
                'revenue.name',
                'revenue.email',
                'revenue.avatar',
                DB::raw('COALESCE(revenue.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(courses.total_courses, 0) as total_courses'),
                DB::raw('COALESCE(students.total_enrolled_students, 0) as total_enrolled_students')
            )
            ->orderByDesc('total_revenue');
    }

    private function getTopCourse()
    {
        $totalRevenue = DB::table('invoices')
            ->select('course_id', DB::raw('SUM(final_amount) as total_revenue'), 'created_at')
            ->where('status', 'Đã thanh toán')
            ->groupBy('course_id', 'created_at');

        $totalStudents = DB::table('course_users')
            ->select('course_id', DB::raw('COUNT(DISTINCT user_id) as total_enrolled_students'))
            ->where(['source' => 'purchase', 'access_status' => 'active'])
            ->groupBy('course_id');

        $totalSales = DB::table('invoices')
            ->select('course_id', DB::raw('COUNT(id) as total_sales'))
            ->where('status', 'Đã thanh toán')
            ->groupBy('course_id');

        return DB::table('courses')
            ->leftJoinSub($totalRevenue, 'invoices', 'invoices.course_id', '=', 'courses.id')
            ->leftJoinSub($totalStudents, 'students', 'students.course_id', '=', 'courses.id')
            ->leftJoinSub($totalSales, 'sales', 'sales.course_id', '=', 'courses.id')
            ->select(
                'courses.id',
                'courses.name',
                'courses.thumbnail',
                'courses.created_at',
                DB::raw('COALESCE(invoices.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(students.total_enrolled_students, 0) as total_enrolled_students'),
                DB::raw('COALESCE(sales.total_sales, 0) as total_sales')
            )
            ->orderByDesc('total_revenue')
            ->take(10);
    }

    private function getTopUser()
    {
        $invoices = DB::table('invoices')
            ->select(
                'user_id',
                DB::raw('COUNT(DISTINCT course_id) as total_courses_purchased'),
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_purchase_date'),
                'created_at'
            )
            ->where('status', 'Đã thanh toán')
            ->groupBy('user_id', 'created_at');

        $course_user = DB::table('course_users')
            ->select('user_id', DB::raw('COUNT(DISTINCT course_id) as total_active_courses'))
            ->where(['source' => 'purchase', 'access_status' => 'active'])
            ->groupBy('user_id');

        return DB::table('users')
            ->leftJoinSub($invoices,  'invoices', 'invoices.user_id', '=', 'users.id')
            ->leftJoinSub($course_user,  'course_users', 'course_users.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('COALESCE(invoices.total_courses_purchased, 0) as total_courses_purchased'),
                DB::raw('COALESCE(invoices.total_spent, 0) as total_spent'),
                DB::raw('COALESCE(invoices.last_purchase_date, NULL) as last_purchase_date')
            )
            ->whereNotNull('invoices.total_courses_purchased')
            ->orderByDesc('total_spent')
            ->take(10);
    }

    private function getSystemFund()
    {
        return DB::table('invoices')
            ->selectRaw('
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            ROUND(SUM(final_amount), 0) as total_revenue,
            ROUND(SUM(final_amount * ?), 0) as total_profit,
            SUM(CASE WHEN invoice_type = "course" THEN 1 ELSE 0 END) as total_course_sales,
            SUM(CASE WHEN invoice_type = "membership" THEN 1 ELSE 0 END) as total_membership_sales,
            SUM(CASE WHEN payment_method = "momo" THEN 1 ELSE 0 END) as total_payment_method_momo,
            SUM(CASE WHEN payment_method = "vnpay" THEN 1 ELSE 0 END) as total_payment_method_vnpay
        ', [self::RATE])
            ->where('status', 'Đã thanh toán')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC');
    }

    private function getCourseRating()
    {
        return DB::table(DB::raw('(SELECT course_id, FLOOR(AVG(rate)) as rating FROM ratings GROUP BY course_id) as subquery'))
            ->join('courses', 'subquery.course_id', '=', 'courses.id')
            ->selectRaw('rating, COUNT(course_id) as total_courses')
            ->groupBy('rating')
            ->orderBy('rating', 'desc');
    }
    private function getTotalByPaymentMethodAndInvoiceType()
    {
        return DB::table('invoices')
            ->selectRaw('
            COUNT(*) as total_invoice,
            COALESCE(COUNT(CASE WHEN invoice_type = "course" THEN 1 END), 0) as total_course_sales,
            COALESCE(COUNT(CASE WHEN invoice_type = "membership" THEN 1 END), 0) as total_membership_sales,
            COALESCE(COUNT(CASE WHEN payment_method = "momo" THEN 1 END), 0) as total_payment_method_momo,
            COALESCE(COUNT(CASE WHEN payment_method = "vnpay" THEN 1 END), 0) as total_payment_method_vnpay
        ')
            ->where('status', 'Đã thanh toán');
    }
}
