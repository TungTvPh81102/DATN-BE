<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Comment;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearnerController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $courseIds = Course::where('user_id', $user->id)->pluck('id');

            $learners = DB::table('course_users')
                ->join('users', 'course_users.user_id', '=', 'users.id')
                ->whereIn('course_users.course_id', $courseIds)
                ->select(
                    'users.id',
                    'users.code',
                    'users.name',
                    'users.email',
                    'users.avatar',
                    DB::raw('MIN(course_users.enrolled_at) as enrolled_at'),
                    DB::raw('COUNT(DISTINCT course_users.course_id) as total_courses')
                )
                ->groupBy(
                    'users.id',
                    'users.code',
                    'users.name',
                    'users.email',
                    'users.avatar'
                )
                ->orderBy('enrolled_at', 'desc')
                ->get();

            if ($learners->isEmpty()) {
                return $this->respondNotFound('Chưa có học viên');
            }

            return $this->respondOk('Danh sách học viên', $learners);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function infoLearner($id)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $learner = User::query()->where('id', $id)->first();

            if (!$learner) {
                return $this->respondNotFound('Không tìm thấy học viên');
            }

            $courseUsers = CourseUser::query()
                ->with([
                    'course',
                    'user'
                ])
                ->where('user_id', $learner->id)
                ->get();

            if ($courseUsers->isEmpty()) {
                return $this->respondNotFound('Học viên chưa mua khóa học nào');
            }

            $courseDetails = $courseUsers->map(function ($courseUser) use ($learner) {
                $totalLessons = Lesson::query()
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->count();

                $completedLessons = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->where('lesson_progress.user_id', $learner->id)
                    ->where('lesson_progress.is_completed', true)
                    ->count();

                $totalTimeSpent = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->where('lesson_progress.user_id', $learner->id)
                    ->sum('lesson_progress.last_time_video');

                $lastStudyTime = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('lesson_progress.user_id', $learner->id)
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->latest('lesson_progress.updated_at')
                    ->value('lesson_progress.updated_at');

                $comments = Comment::query()
                    ->join('lessons', 'comments.commentable_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('comments.commentable_type', Lesson::class)
                    ->where('comments.user_id', '=', $learner->id)
                    ->where('chapters.course_id', $courseUser->course_id)
                    ->select('comments.content', 'comments.commentable_id as lesson_id', 'comments.created_at')
                    ->get();

                $total_comments_overall = Comment::query()
                    ->join('lessons', 'comments.commentable_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('comments.commentable_type', Lesson::class)
                    ->where('comments.user_id', '=', $learner->id)
                    ->count();

                $completion_rate = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

                $totalCompletedByAllUsers = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('lesson_progress.is_completed', true)
                    ->count();

                $totalUsersInCourse = $courseUser
                    ->where('course_id', $courseUser->course_id)
                    ->count();

                $averageCompletionRate = ($totalUsersInCourse > 0 && $totalLessons > 0)
                    ? (($totalCompletedByAllUsers / ($totalUsersInCourse * $totalLessons)) * 100)
                    : 0;

                return [
                    'course_name' => $courseUser->course->name,
                    'course_slug' => $courseUser->course->slug,
                    'enrolled_at' => $courseUser->enrolled_at,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'total_time_spent' => $totalTimeSpent,
                    'last_study_time' => $lastStudyTime,
                    'comments' => $comments,
                    'total_comments_overall' => $total_comments_overall,
                    'completion_rate' => $completion_rate,
                    'average_completion_rate' => $averageCompletionRate,
                    'comparison' => $completion_rate - $averageCompletionRate
                ];
            });

            $totalTimeOverall = $courseDetails->sum('total_time_spent');

            return $this->respondOk('Thông tin tiến độ học tập của: ' . $learner->name, [
                'learner' => [
                    'name' => $learner->name,
                    'email' => $learner->email,
                    'total_courses' => $courseDetails->count(),
                    'total_time_spent' => $totalTimeOverall,
                ],
                'courses' => $courseDetails
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function getLearnerProgress(Request $request, string $code)
    {
        try {
            $instructor = Auth::user();

            if (!$instructor || !$instructor->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $learner = User::query()
                ->where('code', $code)
                ->first();

            if (!$learner) {
                return $this->respondNotFound('Không tìm thấy học viên');
            }

            $courseUsers = CourseUser::query()
                ->with([
                    'course',
                    'user'
                ])
                ->whereHas('course', function ($query) use ($instructor) {
                    $query->where('user_id', $instructor->id);
                })
                ->where('user_id', $learner->id)
                ->get();

            if ($courseUsers->isEmpty()) {
                return $this->respondNotFound('Học viên chưa tham gia khoá học nào');
            }

            $user = [
                'code' => $learner->code,
                'name' => $learner->name,
                'phone' => $learner->phone ?? '',
                'email' => $learner->email,
                'id' => $learner->id
            ];

            $totalCourses = $courseUsers->count();
            $completedCourses = $courseUsers->filter(function ($courseUser) {
                return $courseUser->progress_percent == 100;
            })->count();

            $averageProgress
                = $totalCourses > 0
                ? round(($completedCourses / $totalCourses) * 100, 2)
                : 0;

            $overView = [
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'ongoing_courses' => $totalCourses - $completedCourses,
                'average_progress' => $averageProgress
            ];

            $courses = $courseUsers->map(function ($courseUser) use ($learner) {
                $course = $courseUser->course;

                $recentLesson = LessonProgress::query()
                    ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
                    ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
                    ->where('chapters.course_id', $course->id)
                    ->where('lesson_progress.user_id', $learner->id)
                    ->where('lesson_progress.is_completed', true)
                    ->select(
                        'lessons.id as lesson_id',
                        'lessons.title as lesson_name',
                        'lesson_progress.updated_at as completed_at'
                    )
                    ->orderBy('lesson_progress.updated_at', 'desc')
                    ->first();

                return [
                    'course_id' => $course->id,
                    'name' => $course->name,
                    'progress' => $courseUser->progress_percent,
                    'status' => $courseUser->progress_percent == 100 ? 'completed' : 'learning',
                    'enrolled_at' => $courseUser->enrolled_at,
                    'recent_lesson' => $recentLesson ? [
                        'id' => $recentLesson->lesson_id,
                        'name' => $recentLesson->lesson_name,
                        'completed_at' => $recentLesson->completed_at ?? 'Đang học'
                    ] : null,

                ];
            });

            $studyTime = $this->calculateWeeklyStudyTime($learner->id, $request);
            $certificates = Certificate::query()
                ->where('user_id', $learner->id)
                ->whereHas('course', function ($query) use ($instructor) {
                    $query->where('courses.user_id', $instructor->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($certificate) {
                    return [
                        'id' => $certificate->id,
                        'course_id' => $certificate->course_id,
                        'course_name' => $certificate->course->name ?? 'Khóa học không xác định',
                        'code' => $certificate->certificate_code,
                        'file_path' => $certificate->file_path,
                        'issued_at' => $certificate->created_at,
                    ];
                });

            $response = [
                'user' => $user,
                'overview' => $overView,
                'courses' => $courses,
                'study_time' => $studyTime,
                'certificate' => $certificates
            ];

            return $this->respondOk('Tiến độ học tập của học viên: ' . $learner->name, $response);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    private function calculateWeeklyStudyTime($userId, Request $request)
    {
        $startDate = $request->input('start_date', now()->subWeeks(5)->startOfWeek());
        $endDate = $request->input('end_date', now());

        $weeklyStudyTime = DB::table('lesson_progress')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEARWEEK(created_at) as week_number'),
                DB::raw('MIN(created_at) as week_start'),
                DB::raw('MAX(created_at) as week_end'),
                DB::raw('SUM(last_time_video) as total_hours')
            )
            ->groupBy('week_number')
            ->orderBy('week_number', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'week' => sprintf(
                        "%s - %s",
                        Carbon::parse($item->week_start)->format('d/m'),
                        Carbon::parse($item->week_end)->format('d/m')
                    ),
                    'hours' => round($item->total_hours / 3600, 2)
                ];
            });

        $totalHours = $weeklyStudyTime->sum('hours');

        return [
            'totalHours' => round($totalHours, 2),
            'weeklyData' => $weeklyStudyTime->values()->all()
        ];
    }
}
