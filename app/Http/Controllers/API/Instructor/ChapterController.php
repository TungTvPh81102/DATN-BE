<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Chapters\StoreChapterRequest;
use App\Http\Requests\API\Chapters\UpdateChapterRequest;
use App\Http\Requests\API\Chapters\UpdateOrderChapterRequest;
use App\Models\Course;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;

    public function storeChapter(StoreChapterRequest $request)
    {
        try {
            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $data['slug'])
                ->first();

            if (!$course) {
                throw new \Exception('Không tìm thấy khoá học');
            }

            $lastChapter = $course->chapters()->orderBy('order', 'desc')->first();

            $data['order'] = $lastChapter ? $lastChapter->order + 1 : 1;

            $chapter = $course->chapters()->create($data);

            return $this->respondCreated('Tạo chương học thành công', $chapter);
        } catch (\Exception $e) {
            $this->logError($e->$request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateContentChapter(UpdateChapterRequest $request, string $slug, int $chapterId)
    {
        try {
            $data = $request->validated();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                throw new \Exception('Không tìm thấy khoá học');
            }

            $chapter = $course->chapters()->find($chapterId);

            if (!$chapter) {
                throw new \Exception('Không tìm thấy chương học');
            }

            $chapter->update($data);

            return $this->respondOk('Cập nhật thông tin chương học thành công', $chapter);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateOrderChapter(UpdateOrderChapterRequest $request, string $slug)
    {
        try {
            $data = $request->all();

            $course = Course::query()
                ->with('chapters')
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                throw new \Exception('Không tìm thấy khoá học');
            }

            if (isset($data['chapters'])) {
                foreach ($data['chapters'] as $chapterData) {
                    $chapterToUpdate = $course->chapters()->find($chapterData['id']);
                    $chapterToUpdate->update([
                        'order' => $chapterData['order']
                    ]);
                }
            }

            $chapter = $course->chapters()
                ->with([
                    'lessons'
                ])
                ->orderBy('order')->get();

            return $this->respondOk('Cập nhật thứ tự chương học thành công',
                $chapter
            );
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function deleteChapter(string $slug, int $chapterId)
    {
        try {
            DB::beginTransaction();

            $course = Course::query()
                ->where('slug', $slug)
                ->first();

            if (!$course) {
                throw new \Exception('Không tìm thấy khoá học');
            }

            $chapter = $course->chapters()->find($chapterId);

            if (!$chapter) {
                throw new \Exception('Không tìm thấy chương học');
            }

            if ($chapter->lessons()->count() > 0) {
               return $this->respondError('Chương học không có bài học, không thể xóa');
            }

            $chapter->delete();

            $remainingChapters = $course->chapters()->orderBy('order')->get();

            foreach ($remainingChapters as $index => $chapter) {
                $chapter->update(['order' => $index + 1]);
            }

            DB::commit();

            return $this->respondOk('Xóa chương học thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }

    }

}