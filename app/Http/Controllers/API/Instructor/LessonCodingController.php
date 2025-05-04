<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\StoreCodingLessonRequest;
use App\Http\Requests\API\Lessons\UpdateLessonCodingRequest;
use App\Models\Chapter;
use App\Models\Coding;
use App\Models\Lesson;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonCodingController extends Controller
{
    use ApiResponseTrait, LoggableTrait;

    public function storeLessonCoding(StoreCodingLessonRequest $request, string $chapterId)
    {
        try {
            $data = $request->validated();

            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title']) . '-' . Str::uuid()
                : Str::uuid();

            $chapter = Chapter::query()->where('id', $chapterId)->first();

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            if (!$chapter->course->allow_coding_lesson) {
                return $this->respondForbidden('Khoá học này không cho phép tạo bài học dạng coding');
            }

            $coding = Coding::query()->create([
                'title' => $data['title'],
                'language' => $data['language'],
            ]);

            $data['order'] = $chapter->lessons->max('order') + 1;
            $data['is_supplement'] = (int) $chapter->course->modification_request === 1;

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'coding',
                'lessonable_type' => Coding::class,
                'lessonable_id' => $coding->id,
                'order' => $data['order'],
                'is_free_preview' => $data['is_free_preview'] ?? false,
                'is_supplement' => $data['is_supplement'],
            ]);

            return $this->respondCreated('Tạo bài tập thành công', $lesson);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getCodingExercise(string $slug, string $coding)
    {
        try {
            $lesson = Lesson::query()
                ->where('slug', $slug)
                ->where('lessonable_type', Coding::class)
                ->first();

            if (!$lesson) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            if ($lesson->chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            if ($lesson->chapter->course->status === 'approved' || $lesson->chapter->course->status === 'pending') {
                return $this->respondError('Không thể thực hiện thao tác');
            }

            $coding = $lesson->lessonable->find($coding);

            if (!$coding) {
                return $this->respondNotFound('Không tìm thấy bài tập');
            }

            $coding['content'] = $lesson->content;

            return $this->respondOk('Thông tin bài tập: ' . $lesson->title, $coding);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateCodingExercise(UpdateLessonCodingRequest $request, string $slug, string $coding)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $lesson = Lesson::query()
                ->where('slug', $slug)
                ->where('lessonable_type', Coding::class)
                ->first();

            if (!$lesson) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            if ($lesson->chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $coding = $lesson->lessonable->find($coding);

            if (!$coding) {
                return $this->respondNotFound('Không tìm thấy bài tập');
            }

            if (!empty($data['content'])) {
                $lesson->update([
                    'content' => $data['content']
                ]);
            }

            $data['hints'] = isset($data['hints'])
                ? $data['hints']
                : $coding->hints;

            $coding->update($data);

            DB::commit();

            return $this->respondOk('Cập nhật bài tập thành công', $coding);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function getSampleCode($language)
    {
        return match ($language) {
            'php' => "<?php echo 'Hello, world!';",
            'javascript' => "console.log('Hello, world!');",
            'python' => "print('Hello, world!')",
            'java' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, world!\");\n    }\n}",
            'typescript' => "console.log('Hello, world!');",
            default => throw new \InvalidArgumentException("Không hỗ trợ ngôn ngữ: $language"),
        };
    }
}
