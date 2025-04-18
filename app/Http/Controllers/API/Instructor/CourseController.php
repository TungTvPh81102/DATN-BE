<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Courses\DeleteCourseMultipleRequest;
use App\Http\Requests\API\Courses\RestoreCourseMultipleRequest;
use App\Http\Requests\API\Courses\StoreCourseRequest;
use App\Http\Requests\API\Courses\UpdateContentCourse;
use App\Http\Requests\API\Courses\UpdateCourseObjectives;
use App\Models\Coding;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Document;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToMuxTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, UploadToMuxTrait, ApiResponseTrait;

    const FOLDER_COURSE_THUMBNAIL = 'courses/thumbnail';
    const FOLDER_COURSE_INTRO = 'courses/intro';

    const THUMBNAIL_DEFAULT = 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1741966561/placeholder-16-9-26571_1080x675_zeynku.jpg';

    public function index(Request $request)
    {
        try {
            $type = $request->input('type');

            $courses = Course::query()
                ->where('user_id', Auth::id())
                ->when($type, function ($query, $type) {
                    if ($type === 'practical-course') {
                        return $query->where('is_practical_course', true);
                    } else if ($type === 'course') {
                        return $query->where('is_practical_course', false);
                    }
                    return $query;
                })
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'thumbnail',
                    'intro',
                    'price',
                    'price_sale',
                    'total_student',
                    'status',
                    'is_free',
                    'created_at'
                ])
                ->with([
                    'category:id,name,slug,parent_id',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respondOk(
                'Danh sách khoá học của: ' . Auth::user()->name,
                $courses
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function courseWithPrice()
    {
        try {
            $courses = Course::query()
                ->where('user_id', Auth::id())
                ->select([
                    'id',
                    'name',
                    'thumbnail',
                    'price',
                    'price_sale',
                    'status',
                    'created_at',
                    'category_id'
                ])
                ->with([
                    'category:id,name',
                ])
                ->where('status', 'approved')
                ->where('is_free', 0)
                ->where(function ($query) {
                    $query->where('price', '>', 0)
                        ->orWhere('price_sale', '>', 0);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->respondOk(
                'Danh sách khoá học của: ' . Auth::user()->name,
                $courses
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function courseApproved()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $courses = Course::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('is_free', 0)
                ->select([
                    'id',
                    'code',
                    'name',
                    'thumbnail',
                    'total_student',
                    'price',
                    'created_at'
                ])
                ->get();

            if ($courses->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            return $this->respondOk(
                'Danh sách khoá học đã được kiểm duyệt của: ' . Auth::user()->name,
                $courses
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function courseListOfUser(string $slug)
    {
        try {
            $user = Auth::user();

            $course = Course::query()
                ->select('id', 'user_id', 'code', 'category_id', 'name', 'slug', 'thumbnail', 'intro', 'level', 'price', 'price_sale', 'total_student', 'accepted')
                ->where('slug', $slug)
                ->with([
                    'user:id,name,email,avatar,created_at',
                    'category:id,name,slug,parent_id',
                ])
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== $user->id) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $totalChapter = $course->chapters()->get();

            $totalLesson = $totalChapter->sum(function ($chapter) {
                return $chapter->lessons->count();
            });

            $studentProgress = CourseUser::query()->where('course_id', $course->id)
                ->with(['user:id,name,avatar'])
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'user_id' => $enrollment->user_id,
                        'user_name' => $enrollment->user->name,
                        'user_avatar' => $enrollment->user->avatar,
                        'progress_percent' => $enrollment->progress_percent ?? 0,
                        'enrolled_at' => $enrollment->enrolled_at,
                        'completed_at' => $enrollment->completed_at
                    ];
                });

            return $this->respondOk('Thông tin khoá học:' . $course->name, [
                'course' => $course,
                'total_chapter' => $totalChapter->count(),
                'total_lesson' => $totalLesson,
                'student_progress' => $studentProgress
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function getCourseOverView(string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->with([
                    'user:id,name,email,avatar,created_at',
                    'category:id,name,slug,parent_id',
                    'chapters:id,course_id,title,order',
                    'chapters.lessons'
                ])
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $course->benefits = is_string($course->benefits) ? json_decode($course->benefits, true) : $course->benefits;
            $course->requirements = is_string($course->requirements) ? json_decode($course->requirements, true) : $course->requirements;
            $course->qa = is_string($course->qa) ? json_decode($course->qa, true) : $course->qa;

            return $this->respondOk(
                'Thông tin khoá học: ' . $course->name,
                $course
            );
        } catch (\Exception $e) {

            $this->logError($e);
            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại',
            );
        }
    }

    public function store(StoreCourseRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $data['user_id'] = Auth::id();

            if ($data['user_id'] !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            do {
                $data['code'] = (string)Str::uuid();
                $exits = Course::query()->where('code', $data['code'])->exists();
            } while ($exits);

            $data['slug'] = !empty($data['name'])
                ? Str::slug($data['name']) . '-' . $data['code']
                : $data['code'];

            $course = Course::query()->create([
                'user_id' => $data['user_id'],
                'category_id' => $data['category_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'thumbnail' => self::THUMBNAIL_DEFAULT ?? '',
                'benefits' => json_encode([]),
                'requirements' => json_encode([]),
                'qa' => json_encode([]),
                'is_practical_course' => $data['isPracticalCourse'] ?? false
            ]);

            if (!empty($data['isPracticalCourse']) && $data['isPracticalCourse'] == true) {
                $course->chapters()->create([
                    'title' => $course->name,
                    'order' => 1
                ]);
            }

            DB::commit();

            return $this->respondCreated('Tạo khoá học thành công', $course);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateCourseOverView(UpdateContentCourse $request, string $slug)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== auth()->id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $thumbnailOld = $course->thumbnail ?? null;
            $introOld = $course->intro ?? null;

            $data['slug'] = !empty($data['name'])
                ? Str::slug($data['name']) . '-' . $course->code
                : $course->slug;

            $data['thumbnail'] = $request->hasFile('thumbnail')
                ? $this->handleFileUpload(
                    $request->file('thumbnail'),
                    $thumbnailOld,
                    self::FOLDER_COURSE_THUMBNAIL,
                    'image'
                )
                : $thumbnailOld;

            if ($data['intro'] === null) {
                if ($introOld) {
                    $this->deleteVideo($introOld, self::FOLDER_COURSE_INTRO);
                }
                $data['intro'] = null;
            } else {
                $data['intro'] = $request->hasFile('intro')
                    ? $this->handleFileUpload(
                        $request->file('intro'),
                        $introOld,
                        self::FOLDER_COURSE_INTRO,
                        'video'
                    )
                    : $introOld;
            }

            $course->update($data);

            DB::commit();

            return $this->respondOk(
                'Thao tác thành công',
                $course->load('category')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $this->rollbackFileUploads($data, $thumbnailOld, $introOld);

            $this->logError($e, $request->all());

            if ($e instanceof ValidationException) {
                return $this->respondFailedValidation('Dữ liệu không hợp lệ', $e->errors());
            }

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateCourseObjectives(UpdateCourseObjectives $request, string $slug)
    {
        try {
            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            if ($course->user_id !== Auth::id()) {
                return $this->respondForbidden('Không có quyền thực hiện thao tác');
            }

            $data['requirements'] = array_key_exists('requirements', $data)
                ? (is_string($data['requirements']) ? json_decode($data['requirements'], true) : $data['requirements'])
                : $course->requirements;

            $data['benefits'] = array_key_exists('benefits', $data)
                ? (is_string($data['benefits']) ? json_decode($data['benefits'], true) : $data['benefits'])
                : $course->benefits;

            $data['qa'] = array_key_exists('qa', $data)
                ? (is_string($data['qa']) ? json_decode($data['qa'], true) : $data['qa'])
                : $course->qa;

            $course->update($data);

            return $this->respondOk(
                'Cập nhật mục tiêu khoá học thành công',
                $course->load('category')
            );
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function handleFileUpload($newFile, $oldFile, $folder, $type)
    {
        $uploadFile = $type === 'image'
            ? $this->uploadImage($newFile, $folder)
            : $this->uploadVideo($newFile, $folder);

        if (!empty($oldFile) && filter_var($oldFile, FILTER_VALIDATE_URL)) {
            $type === 'image'
                ? $this->deleteImage($oldFile, $folder)
                : $this->deleteVideo($oldFile, $folder);
        }

        return $uploadFile;
    }

    private function rollbackFileUploads(array $data, $thumbnailOld, $introOld)
    {
        if (!empty($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL)) {
            $this->deleteImage($data['thumbnail'], self::FOLDER_COURSE_THUMBNAIL);
        }

        if (!empty($data['intro']) && filter_var($data['intro'], FILTER_VALIDATE_URL)) {
            $this->deleteVideo($data['intro'], self::FOLDER_COURSE_INTRO);
        }

        $this->deleteFileIfValid($thumbnailOld, self::FOLDER_COURSE_THUMBNAIL, 'image');
        $this->deleteFileIfValid($introOld, self::FOLDER_COURSE_INTRO, 'video');
    }

    private function deleteFileIfValid($file, $folder, $type)
    {
        if (!empty($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            $type === 'image' ? $this->deleteImage($file, $folder) : $this->deleteVideo($file, $folder);
        }
    }

    public function trash(Request $request)
    {
        try {
            $query = $request->input('q');

            $trashedCourses = Course::query()->onlyTrashed()
                ->where('user_id', Auth::id())
                ->select([
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'thumbnail',
                    'intro',
                    'price',
                    'price_sale',
                    'total_student',
                    'status',
                    'is_free',
                    'created_at',
                    'deleted_at'
                ])
                ->with([
                    'category:id,name'
                ])
                ->search($query)
                ->orderBy('deleted_at', 'desc')
                ->get();

            if ($trashedCourses->isEmpty()) {
                return $this->respondOk('Không tìm thấy khóa học nào trong thùng rác', $trashedCourses);
            }

            return $this->respondOk(
                'Danh sách khóa học trong thùng rác của: ' . Auth::user()->name,
                $trashedCourses
            );
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function moveToTrash(DeleteCourseMultipleRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $data = $request->validated();

            $successCount = 0;
            $errorMessages = [];

            foreach ($data['ids'] as $courseId) {
                $course = Course::query()->find($courseId);

                if (!$course) {
                    $errorMessages[] = "Không tìm thấy khoá học với ID {$courseId}";
                    continue;
                }

                if ($course->user_id !== $user->id) {
                    $errorMessages[] = "Bạn không phải là người tạo khóa học: {$course->name}";
                    continue;
                }

                if (!$course->is_practical_course) {
                    if ($course->courseUsers()->count() > 0) {
                        $errorMessages[] = "Khóa học '{$course->name}' đã có học viên đăng ký, không thể xóa";
                        continue;
                    }

                    if ($course->chapters()->count() > 0) {
                        $errorMessages[] = "Khóa học '{$course->name}' đang chứa chương học, không thể xóa";
                        continue;
                    }
                }

                $course->delete();
                $successCount++;
            }

            if ($successCount > 0) {
                return $this->respondOk("Đã chuyển {$successCount} khóa học vào thùng rác");
            } else {
                return $this->respondOk('Không có khóa học nào được chuyển vào thùng rác', $errorMessages);
            }
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function restore(RestoreCourseMultipleRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $data = $request->validated();
            $ids = $data['ids'];

            $coursesToRestore = Course::query()->withTrashed()
                ->whereIn('id', $ids)
                ->where('user_id', Auth::id())
                ->get();

            if ($coursesToRestore->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy khóa học nào để khôi phục');
            }

            foreach ($coursesToRestore as $course) {
                $course->restore();
            }

            return $this->respondOk("Đã khôi phục " . count($coursesToRestore) . " khóa học thành công");
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getChapters(string $slug)
    {
        try {
            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $chapters = $course->chapters()
                ->select([
                    'id',
                    'course_id',
                    'title',
                    'slug',
                    'order'
                ])
                ->orderBy('order')
                ->get();

            return $this->respondOk(
                'Danh sách chương học của khoá học: ' . $course->name,
                $chapters
            );
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function validateCourse(string $slug)
    {
        try {
            $course = $this->findCourseBySlug($slug);

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $authError = $this->authorizeCourseAccess($course);
            if ($authError) return $authError;

            $completionStatus = $this->getCourseCompletionStatus($course);
            $progress = $this->calculateProgress($completionStatus);

            return $this->respondOk('Kiểm tra hoàn thiện khoá học', [
                'progress' => $progress,
                'completion_status' => $completionStatus,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function getValidateCourse(string $code, string $slug)
    {
        try {
            $course = $this->findCourseBySlug($slug);

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $user = User::where('code', $code)->first();

            if ($user && ($user->hasRole('admin') || $user->hasRole('employee'))) {
                $completionStatus = $this->getCourseCompletionStatus($course);
                $progress = $this->calculateProgress($completionStatus);


                return $this->respondOk('Kiểm tra hoàn thiện khoá học', [
                    'progress' => $progress,
                    'completionStatus' => $completionStatus,
                ]);
            } else return;
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }


    public function checkCourseComplete(string $slug)
    {
        try {
            $course = $this->findCourseBySlug($slug);

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khoá học');
            }

            $authError = $this->authorizeCourseAccess($course);
            if ($authError) return $authError;

            return $this->respondOk('Kiểm tra hoàn thiện khoá học', $this->getCourseCompletionStatus($course));
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function calculateProgress(array $completionStatus): float
    {
        $totalSteps = count($completionStatus);
        $completedSteps = 0;

        foreach ($completionStatus as $step) {
            if ($step['status']) {
                $completedSteps++;
            }
        }

        return $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
    }

    private function findCourseBySlug(string $slug)
    {
        return Course::query()->where('slug', $slug)->first();
    }

    private function authorizeCourseAccess(Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            return $this->respondForbidden('Không có quyền thực hiện thao tác');
        }
        return null;
    }

    private function getCourseCompletionStatus(Course $course): array
    {
        $commonValidations = [
            'course_overview' => $this->checkCourseOverView($course),
            'course_objectives' => $this->checkCourseObjectives($course),
        ];

        if (!$course->is_practical_course) {
            $commonValidations['course_curriculum'] = $this->checkCurriculum($course);
        } else {
            $commonValidations['practice_exercise'] = $this->checkPracticeExercise($course);
        }

        return $commonValidations;
    }

    private function checkCourseOverView(Course $course)
    {
        $errors = [];
        $pass = [];

        if (empty($course->name) || strlen($course->name) < 5) {
            $errors[] = "Khóa học phải có tên với tối thiểu 5 ký tự.";
        } else {
            $pass[] = "Khóa học phải có tên với tối thiểu 5 ký tự.";
        }

        if (empty($course->description) || strlen($course->description) < 100) {
            $errors[] = "Mô tả khóa học phải có tối thiểu 100 ký tự.";
        } else {
            $pass[] = "Mô tả khóa học phải có tối thiểu 100 ký tự.";
        }

        if (empty($course->thumbnail)) {
            $errors[] = "Khóa học phải có hình đại diện.";
        } else {
            $pass[] = "Khóa học phải có hình đại diện.";
        }

        if ($course->category()->count() == 0) {
            $errors[] = "Khóa học phải có danh mục.";
        } else {
            $pass[] = "Khóa học phải có danh mục.";
        }

        if (!$course->level) {
            $errors[] = "Khoá học phải có mức độ khó.";
        } else {
            $pass[] = "Khoá học phải có mức độ khó.";
        }

        if (!$course->is_free && (!$course->price || $course->price <= 0)) {
            $errors[] = "Khóa học có phí phải có giá hợp lệ.";
        } else {
            $pass[] = "Khóa học có phí phải có giá hợp lệ.";
        }

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => $pass
        ];
    }

    private function checkCourseObjectives(Course $course)
    {
        $errors = [];
        $pass = [];

        $benefits = $this->decodeJson($course->benefits);
        $requirements = $this->decodeJson($course->requirements);

        if (count($benefits) < 4 || count($benefits) > 10) {
            $errors[] = 'Lợi ích khóa học phải có từ 4 đến 10 mục.';
        } else {
            $pass[] = "Lợi ích khóa học phải có từ 4 đến 10 mục.";
        }

        if (count($requirements) < 4 || count($requirements) > 10) {
            $errors[] = 'Yêu cầu khóa học phải có từ 4 đến 10 mục.';
        } else {
            $pass[] = "Yêu cầu khóa học phải có từ 4 đến 10 mục.";
        }

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => $pass
        ];
    }

    private function checkCurriculum(Course $course)
    {
        $errors = [];
        $pass = [];

        $chapters = $course->chapters()->get();

        if ($chapters->count() === 0) {
            $errors[] = "Khóa học phải có nội dung học tập";
        } else {
            $validateChapter = $this->validateChapters($chapters);
            $pass[] = "Khóa học phải có nội dung học tập";
            $errors = array_merge($errors, $validateChapter["error"]);
            $pass = array_merge($pass, $validateChapter["pass"]);
        }

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => array_keys(array_flip($pass))
        ];
    }

    private function checkPracticeExercise(Course $course)
    {
        $errors = [];
        $pass = [];

        $lessons = $course->lessons()->get();

        if ($lessons->count() < 3) {
            $errors[] = "Bài thực hành phải có ít nhất 3 bài kiểm tra. Hiện tại có {$lessons->count()} bài.";
        } else {
            $pass[] = "Bài thực hành có đủ số lượng bài kiểm tra";

            $hasQuiz = false;

            foreach ($lessons as $lesson) {
                if (empty($lesson->title)) {
                    $errors[] = "Bài kiểm tra ID {$lesson->id} không có tiêu đề";
                }

                if ($lesson->lessonable_type === Quiz::class) {
                    $hasQuiz = true;

                    $quiz = Quiz::query()->find($lesson->lessonable_id);
                    if ($quiz) {
                        $questions = Question::query()->where('quiz_id', $quiz->id)->get();

                        if ($questions->count() < 10) {
                            $errors[] = "Bài kiểm tra '{$lesson->title}' phải có ít nhất 10 câu hỏi";
                        } elseif ($questions->count() > 50) {
                            $errors[] = "Bài kiểm tra '{$lesson->title}' không được vượt quá 50 câu hỏi";
                        } else {
                            $pass[] = "Bài kiểm tra có số lượng câu hỏi hợp lệ";
                        }
                    } else {
                        $errors[] = "Bài kiểm tra '{$lesson->title}' không tồn tại";
                    }
                }
            }

            if (!$hasQuiz) {
                $errors[] = "Bài thực hành phải có ít nhất 1 bài kiểm tra";
            } else {
                $pass[] = "Bài thực hành có bài kiểm tra";
            }
        }

        return [
            'status' => empty($errors),
            'errors' => $errors,
            'pass' => array_keys(array_flip($pass))
        ];
    }

    private function validateChapters($chapters)
    {
        $errors = [];
        $pass = [];

        if ($chapters->count() > 0) {
            if ($chapters->count() < 3) {
                $errors[] = "Khóa học phải có ít nhất 3 chương học. Hiện tại có {$chapters->count()} chương.";
            } else {
                $pass[] = "Khóa học phải có ít nhất 3 chương học.";
            }
        }

        foreach ($chapters as $index => $chapter) {
            if (empty($chapter->title)) {
                $errorsChapter[] = "Chương học ID {$chapter->id} không có tiêu đề.";
            } else {
                $pass[] = "Chương học phải có tiêu đề";
            }

            if ($index === 0) {
                continue;
            }

            if ($chapter->lessons()->count() < 3) {
                $errorsChapter[] = "Chương học '{$chapter->title}' phải có ít nhất 3 bài học. Hiện tại có {$chapter->lessons()->count()} bài.";
            } else {
                $pass[] = "Một chương phải có ít nhất 3 bài học";
            }

            if ($index > 1) {
                $validateChapterVideoDuration = $this->validateChapterVideoDuration($chapter);
                $errors = array_merge($errors, $validateChapterVideoDuration["error"]);
                $pass = array_merge($pass, $validateChapterVideoDuration["pass"]);
            }

            $validateLessons = $this->validateLessons($chapter);
            $errors = array_merge($errors, $validateLessons["error"]);
            $pass = array_merge($pass, $validateLessons["pass"]);
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateChapterVideoDuration($chapter)
    {
        $errors = [];
        $pass = [];

        $totalVideoDuration = Video::query()->whereIn('id', function ($query) use ($chapter) {
            $query->select('lessonable_id')
                ->from('lessons')
                ->where('chapter_id', $chapter->id)
                ->where('lessonable_type', Video::class);
        })->sum('duration');

        if ($totalVideoDuration < 1800) {
            $errors[] = "Tổng thời lượng video trong chương '{$chapter->title}' phải lớn hơn 30 phút. Hiện tại chỉ có " . round($totalVideoDuration / 60, 2) . " phút.";
        } else if ($totalVideoDuration >= 30) {
            $pass[] = "Tổng thời lượng video trong chương phải lớn hơn 30 phút. ( Trừ chương giới thiệu )";
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateLessons($chapter)
    {
        $errors = [];
        $pass = [];
        $lessons = $chapter->lessons()->get();

        foreach ($lessons as $lesson) {
            if (empty($lesson->title)) {
                $errors[] = "Bài học ID {$lesson->id} trong chương '{$chapter->title}' thiếu tiêu đề.";
            } else {
                $pass[] = "Bài học trong chương phải có tiêu đề";
            }

            $validateLessonType = $this->validateLessonType($lesson, $chapter);
            $errors = array_merge($errors, $validateLessonType['error']);
            $pass = array_merge($errors, $validateLessonType['pass']);
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateLessonType($lesson, $chapter)
    {
        $errors = [];
        $pass = [];

        switch ($lesson->lessonable_type) {
            case Video::class:
                $validate = $this->validateVideo($lesson, $chapter);
                $errors = array_merge($errors, $validate["error"]);
                $pass = array_merge($pass, $validate["pass"]);
                break;
            case Quiz::class:
                $validate = $this->validateQuiz($lesson, $chapter);
                $errors = array_merge($errors, $validate["error"]);
                $pass = array_merge($pass, $validate["pass"]);
                break;
            case Coding::class:
                $validate = $this->validateCoding($lesson, $chapter);
                $errors = array_merge($errors, $validate["error"]);
                $pass = array_merge($pass, $validate["pass"]);
                break;
            case Document::class:
                $validate = $this->validateDocument($lesson, $chapter);
                $errors = array_merge($errors, $validate["error"]);
                $pass = array_merge($pass, $validate["pass"]);
                break;
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateVideo($lesson, $chapter)
    {
        $errors = [];
        $pass = [];
        $video = Video::query()->find($lesson->lessonable_id);

        if ($this->isIntroductionLesson($chapter)) {
            return ['error' => $errors, 'pass' => $pass];
        }

        if ($video) {
            if ($video->duration < 120) {
                $errors[] = "Bài giảng '{$lesson->title}' trong chương '{$chapter->title}' có video dưới 2 phút.";
            }

            if ($video->duration > 2400) {
                $errors[] = "Bài giảng '{$lesson->title}' trong chương '{$chapter->title}' có video quá dài (trên 40 phút).";
            }

            if ($video->duration >= 120 && $video->duration <= 2400) {
                $pass[] = "Bài giảng trong chương phải có thời lượng 2 đến 40 phút";
            }
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateQuiz($lesson, $chapter)
    {
        $errors = [];
        $pass = [];
        $quiz = Quiz::query()->find($lesson->lessonable_id);

        if ($quiz) {
            $questions = Question::query()->where('quiz_id', $quiz->id)->get();
            if ($questions->count() < 5 || $questions->count() > 20) {
                $errors[] = "Bài kiểm tra '{$lesson->title}' (ID {$lesson->id}) trong chương '{$chapter->title}' phải có từ 1 đến 10 câu hỏi. Hiện tại có {$questions->count()} câu.";
            } else if ($questions->count() >= 1 && $questions->count() <= 10) {
                $pass[] = "Bài kiểm tra trong chương phải có 1 đến 10 câu hỏi";
            }
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateCoding($lesson, $chapter)
    {
        $errors = [];
        $pass = [];
        $coding = Coding::query()->find($lesson->lessonable_id);

        if ($coding) {
            if (empty($coding->title)) {
                $errors[] = "Bài tập coding trong chương '{$chapter->title}' thiếu tiêu đề.";
            } else {
                $pass[] = "Bài tập coding phải có tiêu đề.";
            }

            if (empty($coding->language)) {
                $errors[] = "Bài tập coding '{$coding->title}' trong chương '{$chapter->title}' chưa chọn ngôn ngữ lập trình.";
            } else {
                $pass[] = "Bài tập coding có chọn ngôn ngữ lập trình.";
            }

            if (empty($coding->sample_code)) {
                $errors[] = "Bài tập coding '{$coding->title}' trong chương '{$chapter->title}' thiếu đoạn mã mẫu (sample code).";
            } else {
                $pass[] = "Bài tập coding phải có đoạn mã mẫu.";
            }
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function validateDocument($lesson, $chapter)
    {
        $errors = [];
        $pass = [];
        $document = Document::query()->find($lesson->lessonable_id);

        if ($document) {
            if (empty($document->title)) {
                $errors[] = "Tài liệu '{$document->title}' trong chương '{$chapter->title}' thiếu tiêu đề.";
            } else {
                $pass[] = "Tài liệu phải có tiêu đề.";
            }

            if (empty($document->content)) {
                $errors[] = "Tài liệu '{$document->title}' trong chương '{$chapter->title}' thiếu nội dung.";
            } else {
                $pass[] = "Tài liệu phải có nội dung.";
            }
        }

        return ['error' => $errors, 'pass' => $pass];
    }

    private function isIntroductionLesson($lesson)
    {
        $introductionKeywords = ['Giới thiệu', 'Introduction', 'Overview'];

        if (isset($lesson->is_introduction) && $lesson->is_introduction) {
            return true;
        }

        foreach ($introductionKeywords as $keyword) {
            if (stripos($lesson->title, $keyword) !== false) {
                return $this->isSimpleIntroduction($lesson);
            }
        }

        if (isset($lesson->is_introduction) && $lesson->is_introduction) {
            return true;
        }

        return false;
    }

    private function isSimpleIntroduction($lesson)
    {
        $nonIntroductionTypes = [Quiz::class, Coding::class];
        if (in_array($lesson->lessonable_type, $nonIntroductionTypes)) {
            return false;
        }

        return true;
    }


    private function decodeJson($value)
    {
        return is_string($value) ? json_decode($value, true) : (array)$value;
    }
}
