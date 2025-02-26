<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Traits\LoggableTrait;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LoggableTrait;
    protected $pdfUrl;
    protected $pdfContent;
    protected $userId;
    protected $courseId;

    public function __construct($pdfUrl, $pdfContent,$userId,$courseId)
    {
        $this->pdfUrl = $pdfUrl;
        $this->pdfContent = $pdfContent;
        $this->userId = $userId;
        $this->courseId = $courseId;
    }

    public function handle()
    {
        try {
            if (!$this->userId || !$this->courseId || !$this->pdfUrl) {
                Log::error('Không đủ dữ liệu ' . $this->userId . '-' . $this->courseId. '-' . $this->pdfUrl);
                return;
            }

            $uploadResult = Cloudinary::upload('data:application/pdf;base64,' . $this->pdfContent, [
                'folder' => 'certificates',
                'resource_type' => 'auto',
                'public_id' => "certificate_{$this->userId}_{$this->courseId}",
            ]);

            $pdfUrl = $uploadResult->getSecurePath();

            if(!$pdfUrl){
                Log::error('Không tạo được link upload lỗi');
                return;
            }

            Certificate::where('user_id', $this->userId)
                ->where('course_id', $this->courseId)
                ->update(['file_path' => $pdfUrl]);

            if (Storage::exists($this->pdfUrl)) {
                Storage::delete($this->pdfUrl);
            }
        } catch (\Exception $e) {
            return $this->logError($e);
        }
    }
}
