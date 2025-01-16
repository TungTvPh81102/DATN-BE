<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SupportBank\StoreGenerateQrRequest;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class SupportBankController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;

    public function index()
    {
        try {

        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }

    public function generateQR(StoreGenerateQrRequest $request)
    {
        try {

        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError(
                'Có lỗi xảy ra, vui lòng thử lại sau'
            );
        }
    }
}
