<?php

use App\Http\Controllers\API\CloudinaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API TEST Base không sử dụng
Route::prefix('v2')->group(function () {
    Route::post('cloudinary', [CloudinaryController::class, 'upload']);
    Route::post('destroy', [CloudinaryController::class, 'delete']);
    Route::post('upload-mux', [CloudinaryController::class, 'mux']);
    Route::get('get-duration/{assetId}', [CloudinaryController::class, 'getVideoDuration']);
    Route::delete('delete-mux/{assetId}', [CloudinaryController::class, 'deleteVideoFromMux']);
});


Route::post('qr', function () {
    $request = request();
    $data = $request->validate([
        'accountNo' => 'required|numeric',
        'accountName' => 'required|string',
        'acqId' => 'required|numeric',
        'amount' => 'required|numeric',
        'addInfo' => 'required|string',
        'format' => 'required|string',
        'template' => 'required|string',
    ]);

    // Gửi yêu cầu tới API VietQR để tạo mã QR
    $response = \Illuminate\Support\Facades\Http::post('https://api.vietqr.io/v2/generate', $data);

    $data = $response->json();
//dd($data);

    $imageData = base64_decode($data['data']['qrDataURL']);

    file_put_contents(storage_path('app/public/qr_code.png'), $imageData);

//    return response()->json([
//        'message' => 'QR code created successfully',
//        'data' => $data,
//        'image' => asset('storage/qr_code.png'),
//        'imageData' => $imageData
//    ]);

    return response()->json(['image_url' => asset('storage/qr_code.png')]);

});

Route::post('/generate-qr', function () {
    $request = request();
    $validated = $request->validate([
        'accountNo' => 'required|numeric',
        'accountName' => 'required|string',
        'acqId' => 'required|numeric',
        'amount' => 'required|numeric',
        'addInfo' => 'nullable|string',
        'format' => 'required|string',
        'template' => 'required|string',
    ]);

    $response = \Illuminate\Support\Facades\Http::post('https://api.vietqr.io/v2/generate', $data);

    if ($response->successful()) {

        // Lấy QR code dưới dạng base64 từ API
        $qrDataURL = $response->json()['data']['qrDataURL'];

        return view('emails.auth.verify', compact('qrDataURL'));
    } else {
        return redirect()->back()->with('error', 'Error generating QR Code');
    }
})->name('qr.generate');
