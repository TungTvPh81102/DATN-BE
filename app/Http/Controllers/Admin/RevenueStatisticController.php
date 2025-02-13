<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueStatisticController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Thống kê doanh thu';

        $totalRevenue = Invoice::query()->sum('final_total');
        $totalCourse = Course::query()
            ->where('status', 'approved')
            ->count();
        $totalInstructor = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })
            ->count();

        $year = now()->year;

        $topInstructors = User::query()
            ->join('courses', 'users.id', '=', 'courses.user_id')
            ->join('invoices', 'courses.id', '=', 'invoices.course_id')
            ->join('course_users', 'courses.id', '=', 'course_users.course_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'instructor');
            })
            ->whereYear('invoices.created_at', $year)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                'users.created_at',
                DB::raw('SUM(invoices.final_total) as total_revenue'),
                DB::raw('COUNT(courses.id) as total_courses'),
                DB::raw('COUNT(DISTINCT course_users.user_id) as total_enrolled_students'),
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar', 'users.created_at')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->paginate(5);

        $topUsers = User::query()
            ->join('invoices', 'users.id', '=', 'invoices.user_id')
            ->join('course_users', 'users.id', '=', 'course_users.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('COUNT(DISTINCT invoices.course_id) as total_courses_purchased'),
                DB::raw('SUM(invoices.final_total) as total_spent'),
                DB::raw('MAX(invoices.created_at) as last_purchase_date')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->having('total_courses_purchased', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(10)
            ->paginate(5);

        $topCourses = Course::query()
            ->join('invoices', 'courses.id', '=', 'invoices.course_id')
            ->join('course_users', 'courses.id', '=', 'course_users.course_id')
            ->whereYear('courses.created_at', '=', $year)
            ->select(
                'courses.id',
                'courses.name',
                'courses.thumbnail',
                'courses.created_at',
                DB::raw('SUM(invoices.final_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT course_users.user_id) as total_enrolled_students'),
                DB::raw('COUNT(invoices.id) as total_sales'),
            )
            ->groupBy('courses.id', 'courses.name', 'courses.thumbnail', 'courses.created_at')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->paginate(5);
            $lastYear = Carbon::now()->subYear()->year;
        $monthlyRevenue = DB::table('invoices')
            ->select(
                DB::raw('MONTH(invoices.created_at) as month'),
                DB::raw('SUM(invoices.final_total) as total_revenue')
            )
            ->whereYear('invoices.created_at', '=', $lastYear)
            ->where('status','completed')
            ->groupBy(DB::raw('MONTH(invoices.created_at)'))
            ->orderBy('month')
            ->get();
            
        if ($request->ajax()) {
            return response()->json([
                'top_courses_table' => view('revenue-statistics.includes.top_courses', compact('topCourses'))->render(),
                'top_instructors_table' => view('revenue-statistics.includes.top_instructors', compact('topInstructors'))->render(),
                'top_users_table' => view('revenue-statistics.includes.top_users', compact('topUsers'))->render(),
                'pagination_links_courses' => $topCourses->links()->toHtml(),
                'pagination_links_instructors' => $topInstructors->links()->toHtml(),
                'pagination_links_users' => $topUsers->links()->toHtml(),
            ]);
        }

        return view('revenue-statistics.index', compact([
            'title',
            'totalRevenue',
            'totalCourse',
            'totalInstructor',
            'topInstructors',
            'topCourses',
            'topUsers',
            'monthlyRevenue'
        ]));
    }
}
