<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Models\Approvable;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\Video;
use App\Traits\FilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class CourseController extends Controller
{
    use FilterTrait;

    public function index(Request $request)
    {
        $title = 'Quản lý khoá học';
        $subTitle = 'Danh sách khoá học trên hệ thống';

        $queryCourses = Course::query()->where('status', 'approved')->with(['user', 'category']);

        if ($request->has('query') && $request->input('query')) {
            $search = $request->input(key: 'query');
            $queryCourses->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }

        $queryCourses = $this->filter($request, $queryCourses);

        $courses = $queryCourses->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {

            $html = view('courses.table', compact('courses'))->render();
            return response()->json(['html' => $html]);
        }

        return view('courses.index', compact('title', 'subTitle', 'courses'));
    }

    public function reject(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->status = 'rejected';
        $course->save();

        // Kiểm tra xem bản ghi đã tồn tại trong bảng approvables chưa
        $approval = Approvable::where('approvable_type', Course::class)
            ->where('approvable_id', $id)
            ->first();

        if ($approval) {
            // Nếu đã có, cập nhật trạng thái & lý do từ chối
            $approval->status = 'rejected';
            $approval->reason = $request->note ?? null;
            $approval->approved_at = now();
            $approval->save();
        } else {
            // Nếu chưa có, tạo bản ghi mới
            $approval = new Approvable();
            $approval->approvable_type = Course::class;
            $approval->approvable_id = $id;
            $approval->approver_id = auth()->id(); // Lưu ai là người từ chối
            $approval->status = 'rejected';
            $approval->reason = $request->note ?? null;
            $approval->request_date = now();
            $approval->approved_at = now();
            $approval->save();
        }
        return redirect()->back()->with('success', 'Khóa học đã bị từ chối.');
    }


    public function show(string $id)
    {
        $course = Course::query()
            ->with('user', 'chapters.lessons.lessonable')
            ->findOrFail($id);

        $courseUsers = CourseUser::query()
            ->where('course_id', $id)
            ->with(['user', 'rating'])
            ->paginate(10);

        // dd($course); 
        $userCounts = CourseUser::query()
            ->where('course_id', $id)
            ->selectRaw('
                COUNT(*) as total_students,
                COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_students,
                COUNT(CASE WHEN completed_at IS NULL AND progress_percent > 0 THEN 1 END) as in_progress_students,
                COUNT(CASE WHEN progress_percent = 0 THEN 1 END) as not_started_students
            ')
            ->first();
        $videos = $course->chapters
            ->flatMap(fn($chapter) => $chapter->lessons)
            ->filter(fn($lesson) => $lesson->lessonable_type === Video::class)
            ->mapToGroups(fn($lesson) => [$lesson->id => $lesson->lessonable]);
        // dd($videos->map(fn($video) => $video->toArray()));
        $recentLessons = LessonProgress::query()
            ->selectRaw('lesson_progress.user_id, lessons.title as lesson_title, MAX(lesson_progress.updated_at) as last_updated')
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('chapters', 'lessons.chapter_id', '=', 'chapters.id')
            ->whereIn('lesson_progress.user_id', collect($courseUsers->items())->pluck('user_id')->toArray()) // Chỉ lấy user trong trang hiện tại
            ->where('chapters.course_id', $id)
            ->groupBy('lesson_progress.user_id', 'lessons.title')
            ->orderBy('last_updated', 'desc')
            ->get()
            ->keyBy('user_id');

        $ratingsData = $this->getCourseRatingBreakdown($id)->get();

        $chapterProgressStats = Chapter::query()
            ->where('course_id', $id)
            ->leftJoin('lessons', 'chapters.id', '=', 'lessons.chapter_id')
            ->leftJoin('lesson_progress', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->selectRaw('chapters.title as chapter_title, 
                        ROUND(AVG(CASE WHEN lesson_progress.is_completed = 1 THEN 100 ELSE lesson_progress.last_time_video END), 2) as avg_progress')
            ->groupBy('chapters.id', 'chapters.title')
            ->get();

        // dd($chapterProgressStats->toArray());

        $totalDuration = $course->chapters->flatMap(function ($chapter) {
            return $chapter->lessons;
        })->filter(function ($lesson) {
            return $lesson->lessonable_type === Video::class;
        })->sum(function ($lesson) {
            return $lesson->lessonable->duration ?? 0;
        });

        $documents = $course->chapters->flatMap(function ($chapter) {
            return $chapter->lessons;
        })->filter(function ($lesson) {
            return $lesson->lessonable_type == Document::class;
        })->mapToGroups(function ($lesson) {
            return [$lesson->id => $lesson->lessonable]  ?? null;
        });

        $quizzes = $course->chapters->flatMap(function ($chapter) {
            return $chapter->lessons;
        })->filter(function ($lesson) {
            return $lesson->lessonable_type == Quiz::class;
        })->mapToGroups(function ($lesson) {

            return [$lesson->id => $lesson->lessonable->load('questions.answers')] ?? null;
        });


        $title = 'Quản lý khoá học';
        $subTitle = 'Thông tin khoá học: ' . $course->name;

        return view('courses.show', compact(
            'title',
            'subTitle',
            'course',
            'courseUsers',
            'userCounts',
            'recentLessons',
            'ratingsData',
            'chapterProgressStats',
            'documents',
            'quizzes',
            'videos',
            'totalDuration',
        ));
    }
    public function updatePopular(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->is_popular = $request->is_popular ? 1 : 0;
        $course->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
    }

    public function export()
    {
        try {
            return Excel::download(new CoursesExport, 'Courses.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function renueveCourse(string $id, Request $request)
    {
        $year = $request->input('year',2025);

        $monthlyRevenue = Invoice::query()
            ->where(['status' => 'Đã thanh toán', 'invoice_type' => 'course', 'course_id' => $id])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, ROUND(SUM(final_amount*(1-instructor_commissions)),0) as total_revenue')
            ->groupBy('year', 'month')
            ->orderByRaw('year ASC, month ASC')
            ->whereYear('created_at',$year)
            ->get();

        return response()->json($monthlyRevenue);
    }
    private function getCourseRatingBreakdown($id)
    {
        return DB::table('ratings')
            ->select('rate as rating', DB::raw('COUNT(*) as total'))
            ->where('course_id', $id)
            ->groupBy('rate')
            ->orderBy('rating', 'asc');
    }

    private function filter($request, $query)
    {
        $filters = [
            'code' => ['queryWhere' => 'LIKE'],
            'name' => ['queryWhere' => 'LIKE'],
            'user_name_course' => null,
            'level' => ['queryWhere' => '='],
            'price' => ['queryWhere' => '='],
            'start_date' => ['queryWhere' => '>='],
            'expire_date' => ['queryWhere' => '<='],
            'status' => ['queryWhere' => '='],
            'created_at' => ['attribute' => 'LIKE'],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
}
