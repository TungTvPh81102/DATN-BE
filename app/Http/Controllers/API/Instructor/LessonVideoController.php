<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\StoreLessonVideoRequest;
use App\Http\Requests\API\Lessons\UpdateLessonVideoRequest;
use App\Models\Chapter;
use App\Models\Coding;
use App\Models\Document;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Video;
use App\Services\VideoUploadService;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonVideoController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait;

    const VIDE0_LESSON = 'videos/lessons';

    protected $videoUploadService;

    public function __construct(VideoUploadService $videoUploadService)
    {
        $this->videoUploadService = $videoUploadService;
    }

    public function storeLessonVideo(StoreLessonVideoRequest $request, string $chapterId)
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

            if ($request->hasFile('video_file')) {
                $dataFile = $this->uploadVideo($request->file('video_file'), self::VIDE0_LESSON, true);
                $muxVideoUrl = $this->videoUploadService->uploadVideoToMux($dataFile['secure_url']);

                if (!$muxVideoUrl) {
                    return $this->respondServerError('Có lỗi xảy ra khi upload video, vui lòng thử lại');
                }
                
                sleep(5);
                $duration = $this->videoUploadService->getVideoDurationToMux($muxVideoUrl['asset_id']);

                $video = Video::query()->create([
                    'title' => $data['title'],
                    'url' => $dataFile['secure_url'],
                    'asset_id' => $muxVideoUrl['asset_id'],
                    'mux_playback_id' => $muxVideoUrl['playback_id'],
                    'duration' => $duration,
                ]);
            }

            $data['order'] = $chapter->lessons->max('order') + 1;

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'video',
                'lessonable_type' => Video::class,
                'lessonable_id' => $video->id,
                'order' => $data['order'],
                'content' => $data['content'] ?? null,
                'is_free_preview' => $data['is_free_preview'] ?? false,
            ]);

            return $this->respondCreated('Tạo bài giảng thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getLessonVideo(string $chapterId, string $lessonId)
    {
        try {
            $chapter = Chapter::query()->where('id', $chapterId)->first();

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $lesson = Lesson::query()->where('id', $lessonId)->first();

            if (!$lesson) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            if ($lesson->chapter_id !== $chapter->id) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            if ($lesson->lessonable_type !== Video::class) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            return $this->respondOk('Lấy thông tin bài giảng thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateLessonVideo(UpdateLessonVideoRequest $request, string $chapterId, string $lessonId)
    {
        try {
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

            $lesson = Lesson::query()->where('id', $lessonId)->first();

            if (!$lesson || $lesson->chapter_id !== $chapter->id || $lesson->lessonable_type !== Video::class) {
                return $this->respondNotFound('Không tìm thấy bài giảng');
            }

            $video = $lesson->lessonable;
            if ($request->hasFile('video_file')) {
                $this->deleteVideo($video->url, self::VIDE0_LESSON);
                $this->videoUploadService->deleteVideoFromMux($video->asset_id);

                $dataFile = $this->uploadVideo($request->file('video_file'), self::VIDE0_LESSON, true);
                $muxVideoUrl = $this->videoUploadService->uploadVideoToMux($dataFile['secure_url']);

                if (!$muxVideoUrl) {
                    return $this->respondServerError('Có lỗi xảy ra khi upload video, vui lòng thử lại');
                }

                sleep(5);
                $duration = $this->videoUploadService->getVideoDurationToMux($muxVideoUrl['asset_id']);

                $video->update([
                    'title' => $data['title'],
                    'url' => $dataFile['secure_url'],
                    'asset_id' => $muxVideoUrl['asset_id'],
                    'mux_playback_id' => $muxVideoUrl['playback_id'],
                    'duration' => $duration,
                ]);
            }

            $lesson->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'is_free_preview' => $data['is_free_preview'] ?? false,
            ]);

            return $this->respondOk('Cập nhật bài giảng thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
