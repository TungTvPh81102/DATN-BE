<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Documents\DocumentRequest;
use App\Http\Requests\API\Lessons\UpdateLessonDocumetRequest;
use App\Models\Chapter;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\Video;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    use LoggableTrait, UploadToLocalTrait, ApiResponseTrait;

    const DOCUMENT_LESSON = 'documents/lessons';

    public function index(string $id)
    {
        try {
            $documents = Document::query()
                ->whereHas('lessons', function ($query) use ($id) {
                    $query->where('id', $id);
                })
                ->latest('id')->get();

            return response()->json([
                'message' => 'Danh sách tài liệu',
                'status' => true,
                'document' => $documents
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Không thể tải danh sách tài liệu, vui lòng thử lại');
        }
    }

    public function getLessonDocument(string $chapterId, string $lessonId)
    {
        try {
            $chapter = Chapter::query()->find($chapterId);

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $lesson = Lesson::query()->with('lessonable')->find($lessonId);

            if (!$lesson || $lesson->chapter_id !== $chapter->id) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            if ($lesson->lessonable_type !== Document::class) {
                return $this->respondNotFound('Bài giảng không phải là tài liệu');
            }

            return $this->respondOk('Lấy thông tin tài liệu thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function storeLessonDocument(DocumentRequest $request, string $chapterId)
    {
        try {
            DB::beginTransaction();

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

            if ($request->hasFile('document_file')) {
                $documentFile = $request->file('document_file');
                $data['file_path'] = $this->uploadToLocal($documentFile, self::DOCUMENT_LESSON);
                $data['file_type'] = 'upload';
            } elseif (!empty($request->document_url)) {
                $data['file_path'] = $request->document_url;
                $data['file_type'] = 'url';
            }

            $document = Document::query()->create($data);

            $data['order'] = $chapter->lessons->max('order') + 1;
            $data['is_supplement'] = (int) $chapter->course->modification_request === 1;

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'document',
                'lessonable_type' => Document::class,
                'lessonable_id' => $document->id,
                'order' => $data['order'],
                'content' => $data['content'] ?? null,
                'is_free_preview' => $data['is_free_preview'] ?? false,
                'is_supplement' => $data['is_supplement'],
            ]);

            DB::commit();

            return $this->respondCreated('Tạo tài liệu thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function updateLessonDocument(UpdateLessonDocumetRequest $request, string $chapterId, string $lessonId)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $chapter = Chapter::query()->find($chapterId);

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->status === 'approved' || $chapter->course->status === 'pending') {
                return $this->respondError('Không thể thực hiện thao tác');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $lesson = Lesson::query()
                ->with('lessonable')
                ->where('id', $lessonId)->first();

            if (!$lesson || $lesson->chapter_id !== $chapter->id || $lesson->lessonable_type !== Document::class) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            $document = $lesson->lessonable;
            if ($request->hasFile('document_file')) {
                if ($document->file_path && Storage::exists($document->file_path)) {
                    $this->deleteFromLocal($document->file_path, self::DOCUMENT_LESSON);
                }
                $documentFile = $request->file('document_file');
                $data['file_path'] = $this->uploadToLocal($documentFile, self::DOCUMENT_LESSON);
                $data['file_type'] = 'upload';
            } elseif (!empty($request->document_url)) {
                if ($document->file_path && Storage::exists($document->file_path)) {
                    $this->deleteFromLocal($document->file_path, self::DOCUMENT_LESSON);
                }
                $data['file_path'] = $request->document_url;
                $data['file_type'] = 'url';
            } else {
                $data['file_path'] = $document->file_path;
                $data['file_type'] = $document->file_type;
            }

            $data['title'] = !empty($data['title']) ? $data['title'] : Str::uuid();

            $lesson->lessonable->update([
                'title' => $data['title'],
                'file_path' => $data['file_path'],
                'file_type' => $data['file_type'],
                'content' => $data['content'] ?? $lesson->content,
            ]);

            $lesson->update([
                'title' => $data['title'],
                'is_free_preview' => $data['is_free_preview'] ?? $lesson->is_free_preview,
                'content' => $data['content'] ?? $lesson->content,
            ]);

            DB::commit();

            return $this->respondSuccess('Cập nhật tài liệu thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function destroy(string $id)
    {
        try {
            $document = Document::find($id);

            if (!$document) {
                return $this->respondNotFound('Tài liệu không tồn tại');
            }

            if ($document->file_type === 'upload' && Storage::exists($document->file_path)) {
                $this->deleteFromLocal($document->file_path, self::DOCUMENT_LESSON);
            }

            $document->delete();

            return $this->respondNoContent();
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
