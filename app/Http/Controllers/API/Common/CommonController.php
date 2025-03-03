<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function instructorOrderByCountCourse(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $page == 1 ? 4 : 2;
            $maxInstructors = 10;


            $query = User::query()->role('instructor')
                ->whereHas('courses', function ($query) {
                    $query->where('status', 'approved');
                })
                ->withCount(['courses' => function ($query) {
                    $query->where('status', 'approved');
                }])
                ->orderByDesc('courses_count');

            $totalInstructors = $query->count();

            $instructors = $query
                ->take($maxInstructors)
                ->skip(($page - 1) * $perPage)
                ->get()
                ->map(function ($instructor) {
                    return [
                        'id' => $instructor->id,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                        'avatar' => $instructor->avatar_url,
                        'total_approved_courses' => $instructor->courses_count
                    ];
                });

            $hasMore = $totalInstructors > ($page * $perPage);

            return $this->respondOk('Danh sách giảng viên theo số lượng khoá học', [
                'instructors' => $instructors,
                'has_more' => $hasMore
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
