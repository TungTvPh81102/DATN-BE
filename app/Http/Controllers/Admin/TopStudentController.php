<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DashBoardExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TopStudentController extends Controller
{
    use LoggableTrait;
    public function index(Request $request)
    {
        try {
            $title = 'Dashboard';
            $year = now()->year;
            $queryTopUsers = $this->getTopUser($request);

            // list(
            // ) = $this->getFilterDataChart(
            //     $request,
            // );

            $topUsers = DB::table(DB::raw("({$queryTopUsers->toSql()}) as sub"))
                ->mergeBindings($queryTopUsers)
                ->paginate(5);

            if ($request->ajax()) {
                return response()->json([
                    'top_users_table' => view('revenue-statistics.includes.top_users', compact('topUsers'))->render(),
                    'pagination_links_users' => $topUsers->links()->toHtml(),
                    'topUsers' => $topUsers,
                ]);
            }

            return view('top-students.index', compact([
                'title',
                'topUsers',
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
                ->where("{$table}.{$column}", '<=', now()->endOfDay());
        }

        return $query;
    }
    private function getTopUser(Request $request)
    {
        $invoices = DB::table('invoices')
            ->select(
                'user_id',
                DB::raw('COUNT(DISTINCT course_id) as total_courses_purchased'),
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_purchase_date'),
            )
            ->where('status', 'Đã thanh toán')
            ->orderByDesc('total_spent')
            ->groupBy('user_id');

        $invoices = $this->applyGlobalFilter($invoices, $request, 'invoices', 'created_at');

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
}
