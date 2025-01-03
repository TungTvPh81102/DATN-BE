<?php

namespace App\Traits;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

trait UploadToCloudinaryTrait
{
    use LoggableTrait, ApiResponseHelpers;

    public function uploadImage($file, $folder = null)
    {
        try {
            if (!$file->isValid()) {
                return null;
            }

            $uploadResult =  Cloudinary::upload($file->getRealPath(), [
                'folder' => $folder ?? 'images',
                'public_id' => Str::random(10),
            ]);

            return $uploadResult->getSecurePath() ?? null;
            
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadVideo($file, $folder = null)
    {
        try {
            if (!$file->isValid()) {
                return null;
            }

            $uploadResult =  Cloudinary::uploadVideo($file->getRealPath(), [
                'folder' => $folder ?? 'videos',
                'public_id' => Str::random(10),
                'format' => 'mp4',
            ]);

            $secure_url = $uploadResult->getSecurePath() ?? null;
            $duration = $uploadResult->getDuration() ?? null;
            $publicId = $uploadResult->getPublicId() ?? null;

            return [
                'secure_url' => $secure_url,
                'duration' => $duration,
                'public_id' => $publicId,
            ];
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteImage($dataUrl, $folder = null)
    {
        try {
            if (empty($dataUrl) || !filter_var($dataUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('URL không hợp lệ hoặc không được cung cấp.');
            }
            $publicId = pathinfo(parse_url($dataUrl, PHP_URL_PATH), PATHINFO_FILENAME);

            $publicIdWithFolder = ($folder ?? 'images')   . '/' . $publicId;

            $deleteResult = Cloudinary::destroy($publicIdWithFolder);

            if ($deleteResult['result'] !== 'ok') {
                throw new \Exception('Không thể xóa ảnh');
            }

            return true;
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteVideo($dataUrl, $folder = null)
    {
        try {
            if (empty($dataUrl) || !filter_var($dataUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('URL không hợp lệ hoặc không được cung cấp.');
            }
            $publicId = pathinfo(parse_url($dataUrl, PHP_URL_PATH), PATHINFO_FILENAME);

            $publicIdWithFolder = ($folder ?? 'images') . '/' . $publicId;

            $deleteResult = Cloudinary::destroy($publicIdWithFolder, [
                'resource_type' => 'video',
            ]);

            if ($deleteResult['result'] !== 'ok') {
                throw new \Exception('Không thể xóa video');
            }

            return true;
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
