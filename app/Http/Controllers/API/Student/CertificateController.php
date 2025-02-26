<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Jobs\UploadCertificateJob;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseUser;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    public function generateCertificate(string $slug)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập, vui lòng đăng nhập và thử lại.');
            }

            $course = Course::query()->where('slug', $slug)->first();

            if (!$course) {
                return $this->respondNotFound('Không tìm thấy khóa học.');
            }

            $courseUser = CourseUser::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$courseUser) {
                return $this->respondForbidden('Người dùng chưa mua khóa học này.');
            }

            if ($courseUser->progress_percent !== 100 || $courseUser->completed_at == null) {
                return $this->respondError('Tiến độ chưa đạt 100%.');
            }

            $certificate = Certificate::query()->where(['user_id' => $user->id, 'course_id' => $course->id])->first();

            if (!$certificate) {
                $certificate_code = 'CERT-' . $user->id . $course->id . random_int(111111, 999999);
                $certificateTemplate = CertificateTemplate::query()->where('status', 1)->first();
                $ngayHoanThanh = Carbon::now(env('APP_TIMEZONE'))->locale('vi')->translatedFormat('d F, Y');

                if (!$certificateTemplate) {
                    return $this->respondError('Không có mẫu chứng chỉ');
                }

                $images = [
                    'logo_course_mely' => public_path('assets/images/logo-container.png'),
                    'dau_do' => public_path('assets/images/daudocoursemely.png'),
                    'background_template' => $certificateTemplate->background,
                ];

                $variables = [
                    'user_name' => $user->name,
                    'course_name' => $course->name,
                    'instructor_name' => $course->user->name,
                    'dau_do' => $images['dau_do'] ?? '#',
                    'logo_course_mely' =>  $images['logo_course_mely'] ?? '#',
                    'chung_nhan_so' => $certificate_code,
                    'ngay_hoan_thanh' => $ngayHoanThanh,
                    'background_template' => $images['background_template'] ?? '#',
                ];

                $templateVariables = json_decode($certificateTemplate->variables);
                $content = $certificateTemplate->content;

                foreach ($templateVariables as $key => $placeholder) {
                    if (array_key_exists($key, $variables)) {
                        $content = str_replace($key, $variables[$key], $content);
                    }
                }

                $start = microtime(true);

                $pdf = Pdf::loadHTML($content)->setPaper('A4', 'landscape');
                $filename = "certificate_{$user->id}_{$course->id}.pdf";
                $pdfContent = $pdf->output();

                $end = microtime(true);
                Storage::disk('public')->put("certificates/{$filename}",  $pdfContent);
                $pdfUrl = Storage::url("certificates/{$filename}");

                if (!$pdfUrl) {
                    return $this->respondError('Không thể tạo đường dẫn chứng chỉ');
                }

                $certificate = Certificate::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'certificate_template_id' => $certificateTemplate->id,
                    'certificate_code' => $certificate_code,
                    'issued_at' => now(env('APP_TIMEZONE')),
                    'file_path' => $pdfUrl,
                ]);

                UploadCertificateJob::dispatch('certificates/' . $filename, base64_encode($pdfContent), $user->id, $course->id)
                    ->delay(now()->addSeconds(3600));
            }

            return $this->respondOk('Tạo đường dẫn chứng chỉ thành công', ['pdf_url' => $certificate->file_path]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, chưa thể tạo chứng chỉ');
        }
    }
}
