<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Models\QaSystem;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class QaSystemController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;

    public function index()
    {
        try {
            $qaSystem = QaSystem::query()
                ->where('status', 1)
                ->latest('id')
                ->get();

            if ($qaSystem->isEmpty()) {
                return $this->respondNotFound(
                    'Không tìm thấy dữ liệu'
                );
            }

            return $this->respondOk('Danh sách câu hỏi của hệ thống', $qaSystem);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }
}
