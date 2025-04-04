<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StatisticController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getCourseOverview()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $totalCourse = Course::query()->where('user_id', $user->id)->count();

            $totalEnrollments = DB::table('course_users')
                ->join('courses', 'course_users.course_id', '=', 'courses.id')
                ->where('courses.user_id', $user->id)
                ->count();

            $totalRevenue = DB::table('invoices')->selectRaw('ROUND(SUM(final_amount)*0.6, 2) as total_revenue')->where('invoices.status',  "Đã thanh toán")
                ->join('courses', 'invoices.course_id', '=', 'courses.id')
                ->where('courses.user_id', $user->id)
                ->first();

            $averageRating = DB::table('ratings')->selectRaw('ROUND(AVG(ratings.rate), 1) as avg_rating')
                ->join('courses', 'ratings.course_id', '=', 'courses.id')
                ->where('courses.user_id', $user->id)->first();

            return $this->respondOk('Dữ liệu thông kê tổng quan của giảng viên ' . $user->name, [
                'totalCourse' => $totalCourse,
                'totalEnrollments' => $totalEnrollments,
                'totalRevenue' => $totalRevenue->total_revenue,
                'averageRating' => $averageRating->avg_rating
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Lấy dữ liệu thống kê tổng quan của giảng viên không thành công');
        }
    }

    public function getCourseRevenue(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $courses = DB::table('courses')
                ->select(
                    'courses.id',
                    'courses.name',
                    'courses.price',
                    'courses.price_sale',
                    'courses.slug',
                    'courses.thumbnail',
                    'courses.total_student',
                    'courses.category_id'
                )->where(['courses.status' => 'approved', 'courses.user_id' => $user->id]);

            $invoices = DB::table('invoices')
                ->select('invoices.course_id', DB::raw('SUM(invoices.final_amount) as total_revenue'))
                ->joinSub($courses, 'courses', function ($join) {
                    $join->on('courses.id', '=', 'invoices.course_id');
                })->groupBy('invoices.course_id');

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
                    'courses.price',
                    'courses.price_sale',
                    'courses.slug',
                    'courses.thumbnail',
                    'course_users.total_student',
                    'categories.name as name_category',
                    'categories.slug as slug_category',
                    'categories.icon as icon_category',
                    DB::raw('ROUND(COALESCE(invoices.total_revenue)*0.6, 2) as total_revenue'),
                    DB::raw('ROUND(COALESCE(course_users.avg_progress),2) as avg_progress'),
                    DB::raw('ROUND(COALESCE(ratings.avg_rating), 1) as avg_rating')
                )
                ->leftJoinSub($invoices,'invoices', function($join){
                    $join->on('invoices.course_id', '=', 'courses.id');
                })
                ->leftJoinSub($ratings,'ratings', function($join){
                    $join->on('ratings.course_id', '=', 'courses.id');
                })
                ->leftJoinSub($course_users,'course_users', function($join){
                    $join->on('course_users.course_id', '=', 'courses.id');
                })
                ->join('categories', 'categories.id', '=', 'courses.category_id')
                ->orderByDesc('total_revenue')
                ->get();

            return $this->respondOk('Doanh thu khóa học của giảng viên ' . $user->name,  $courseRevenue);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getMonthlyRevenue(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $yearNow = now()->year;

            $year = $request->input('year', $yearNow);
            if ($year > $yearNow) $year = $yearNow;

            $monthlyRevenue = DB::table('invoices')
                ->selectRaw('MONTH(invoices.created_at) as month, ROUND(SUM(final_amount) * 0.6, 2) as revenue')
                ->join('courses', function ($join) use ($user) {
                    $join->on('invoices.course_id', '=', 'courses.id')->where('courses.user_id', $user->id);
                })
                ->where('invoices.status', 'Đã thanh toán')
                ->whereYear('invoices.created_at', $year)
                ->groupBy('month')
                ->pluck('revenue', 'month')
                ->toArray();

            $allMonths = [];

            for ($i = 1; $i <= 12; $i++) {
                $allMonths[$i] = $monthlyRevenue[$i] ?? null;
            }

            return $this->respondOk('Doanh thu theo tháng của giảng viên ' . $user->name, $allMonths);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getRatingStats()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $courseIds = Course::query()->where('user_id', $user->id)->pluck('id');

            if ($courseIds->isEmpty()) {
                return $this->respondNotFound('Không có dữ liệu rating cho các khóa học.');
            }

            $ratingStats = Rating::query()->whereIn('course_id', $courseIds)
                ->selectRaw('rate, COUNT(*) as count')
                ->groupBy('rate')
                ->get();

            $totalRatings = $ratingStats->sum('count');

            $data = $ratingStats->map(function ($stat) use ($totalRatings) {
                return [
                    'rate' => $stat->rate,
                    'count' => $stat->count,
                    'percentage' => $totalRatings > 0
                        ? round(($stat->count / $totalRatings) * 100, 2)
                        : 0,
                ];
            });

            return $this->respondOk('Bao cáo tỷ lệ đánh giá', $data);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getMonthlyCourseStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $yearNow = now()->year;
            $year = $request->input('year', $yearNow);
            if ($year > $yearNow) $year = $yearNow;

            $monthlyPurchases = DB::table('invoices')
                ->selectRaw('MONTH(invoices.created_at) as month, COUNT(invoices.id) as total_purchases')
                ->join('courses', function ($join) use ($user) {
                    $join->on('invoices.course_id', '=', 'courses.id')->where(['courses.status' => 'approved', 'courses.user_id' => $user->id]);
                })
                ->where('invoices.status', 'Đã thanh toán',)
                ->whereYear('invoices.created_at', $year)
                ->groupBy('month')
                ->pluck('total_purchases', 'month')
                ->toArray();

            $monthlyStudents = DB::table('course_users')
                ->selectRaw('MONTH(course_users.created_at) as month, COUNT(DISTINCT course_users.user_id) as total_students')
                ->join('courses', function ($join) use ($user) {
                    $join->on('course_users.course_id', '=', 'courses.id')->where(['courses.status' => 'approved', 'courses.user_id' => $user->id]);
                })
                ->where(['source' => 'purchase', 'access_status' => 'active'])
                ->whereYear('course_users.created_at', $year)
                ->groupBy('month')
                ->pluck('total_students', 'month')
                ->toArray();

            $allMonths = [];
            for ($i = 1; $i <= 12; $i++) {
                $allMonths[$i] = [
                    'total_purchases' => $monthlyPurchases[$i] ?? 0,
                    'total_students'  => $monthlyStudents[$i] ?? 0,
                ];
            }

            return $this->respondOk('Thống kê lượt mua và học viên theo tháng', $allMonths);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getTotalSalesByMonth(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $yearNow = now()->year;
            $year = $request->input('year', $yearNow);
            if ($year > $yearNow) $year = $yearNow;

            // Lấy tổng số lượng bán theo tháng
            $totalMembership = DB::table('invoices')
                ->selectRaw('MONTH(invoices.created_at) as month, 
                    SUM(CASE WHEN invoices.invoice_type = "membership" THEN 1 ELSE 0 END) as total_membership')
                ->join('courses', function ($join) use ($user) {
                    $join->on('invoices.course_id', '=', 'courses.id')->where('courses.user_id', $user->id);
                })
                ->whereYear('invoices.created_at', $year)
                ->groupBy('month')
                ->pluck('total_membership', 'month')
                ->toArray();

            $totalCourse = DB::table('invoices')
                ->selectRaw('MONTH(invoices.created_at) as month, 
                    SUM(CASE WHEN invoices.invoice_type = "course" THEN 1 ELSE 0 END) as total_course')
                ->join('courses', function ($join) use ($user) {
                    $join->on('invoices.course_id', '=', 'courses.id')->where('courses.user_id', $user->id);
                })
                ->whereYear('invoices.created_at', $year)
                ->groupBy('month')
                ->pluck('total_course', 'month')
                ->toArray();


            $monthlySales = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlySales[$i] = [
                    'month' => $i,
                    'total_membership' => $totalMembership[$i] ?? 0,
                    'total_course' => $totalCourse[$i] ?? 0,
                ];
            }

            return $this->respondOk('Thống kê lượt mua và học viên theo tháng', $monthlySales);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
