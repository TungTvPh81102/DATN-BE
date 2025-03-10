<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\BuyCourseRequest;
use App\Http\Requests\API\Transaction\DepositTransactionRequest;
use App\Mail\StudentCoursePurchaseMail;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Invoice;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\InstructorNotificationForCoursePurchase;
use App\Notifications\JoiFreeCourseNotification;
use App\Notifications\UserBuyCourseNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Return_;

class TransactionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    const adminRate = 0.4;
    const instructorRate = 1 - self::adminRate;
    const walletMail = 'quaixe121811@gmail.com';

    public function index()
    {
        try {
            $transactions = Transaction::query()->where('transactionable_id', Auth::id())->latest('id')->get();

            return $this->respondOk('Danh sách giao dịch của: ' . Auth::user()->name, $transactions);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function show(string $id)
    {
        try {
            $transaction = Transaction::query()->findOrFail($id);

            return $this->respondOk('Chi tiết giao dịch của: ' . Auth::user()->name, $transaction);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function enrollFreeCourse(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'course_id' => 'required|exists:courses,id',
            ]);

            $userId = Auth::id();
            $courseId = $request->course_id;

            if (!$userId) {
                return $this->respondForbidden('Vui lòng đăng nhập để tham gia khoá học');
            }

            $course = Course::query()->find($courseId);

            if (CourseUser::query()->where([
                'user_id' => $userId,
                'course_id' => $courseId,
            ])->exists()) {
                return $this->respondOk('Bạn đã đã tham gia khoá học này ồi');
            }

            if ($course->price_sale > 0 && $course->price > 0) {
                return $this->respondError('Khóa học không phải miễn phí');
            }

            CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            $course->increment('total_student');

            $instructor = $course->user;

            $instructor->notify(
                new JoiFreeCourseNotification(Auth::user(), $course)
            );

            DB::commit();

            return $this->respondOk('Tham gia khoá học thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function deposit(DepositTransactionRequest $request)
    {
        try {
            $data = $request->validated();

            $deposit = Transaction::query()->create([
                'amount' => $request->amount,
                'coin' => round($request->amount / 1000, 2),
                'transactionable_id' => Auth::id(),
                'transactionable_type' => 'App\Models\User',
            ]);

            return response()->json([
                'message' => 'Giao dịch nạp tiền đang chờ xử lý',
                'deposit' => $deposit,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Nạp tiền thất bại, vui lòng thử lại',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createVNPayPayment(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Vui lòng đăng nhập để mua khóa học');
            }

            $validated = $request->validate([
                'amount' => 'required|numeric',
                'course_id' => 'required|exists:courses,id',
                'coupon_code' => 'nullable|string',
            ]);

            $hasBoughtCourse = CourseUser::query()
                ->where('user_id', $user->id)
                ->where('course_id', $validated['course_id'])
                ->exists();

            if ($hasBoughtCourse) {
                return $this->respondError('Bạn đã sở hữu khoá học này rồi');
            }

            $couponResponse
                = $this->checkCouponNoJson($validated['coupon_code'], $validated['amount'], $validated['course_id']);
            if (!$couponResponse['success']) {
                return $this->respondError($couponResponse['error']);
            }

            $couponData = $couponResponse['data'];
            $finalAmount = $couponData['final_amount'];
            $amountVNPay = number_format($finalAmount, 0, '', '');

            $vnp_TmnCode = config('vnpay.tmn_code');
            $vnp_HashSecret = config('vnpay.hash_secret');
            $vnp_Url = config('vnpay.url');
            $vnp_ReturnUrl = config('vnpay.return_url');

            $vnp_TxnRef = 'ORDER' . time();
            $vnp_OrderInfo = $user->id . '-Thanh-toan-khoa-hoc-' . $validated['course_id'];
            if (!empty($validated['coupon_code'])) {
                $vnp_OrderInfo .= '-' . $validated['coupon_code'];
            }
            $vnp_Locale = 'vn';
            $vnp_IpAddr = request()->ip();

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_Command" => "pay",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $amountVNPay * 100,
                "vnp_CreateDate" => now()->format('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_ReturnUrl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";

            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }


            return $this->respondOk('Tạo link thanh toán thành công', $vnp_Url);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function vnpayCallback(Request $request)
    {
        try {
            $vnp_HashSecret = config('vnpay.hash_secret');
            $frontendUrl = config('app.fe_url') . "/payment";

            $inputData = $request->all();
            if (!isset($inputData['vnp_SecureHash'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);

            $hashData = urldecode(http_build_query($inputData));
            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            // Nếu thanh toán không thành công
            if ($inputData['vnp_ResponseCode'] != '00') {
                return redirect()->away($frontendUrl . "?status=failed");
            }

            DB::beginTransaction();

            if (!isset($inputData['vnp_OrderInfo'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $orderInfo = explode('-', str_replace('-Thanh-toan-khoa-hoc-', '-', $inputData['vnp_OrderInfo']));

            if (count($orderInfo) < 2) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $userId = filter_var(trim($orderInfo[0], '"'), FILTER_VALIDATE_INT);
            $courseId = filter_var(trim($orderInfo[1], '"'), FILTER_VALIDATE_INT);
            $couponCode = isset($orderInfo[2]) ? trim($orderInfo[2], '"') : null;

            $user = User::query()->find($userId);
            $course = Course::query()->find($courseId);

            if (!$user) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            if (!$course) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            $originalAmount = $inputData['vnp_Amount'] / 100;
            $discountAmount = 0;
            $finalAmount = $originalAmount;

            // Kiểm tra mã giảm giá (nếu có)
            $discount = null;
            if (!empty($couponCode)) {
                $discount = Coupon::query()
                    ->where(['code' => $couponCode, 'status' => '1'])
                    ->first();
                if ($discount) {
                    $discountAmount = $discount->type === 'percent'
                        ? min(($originalAmount * $discount->discount_value) / 100, $discount->discount_max_value ?? $originalAmount)
                        : min($discount->discount_value, $originalAmount);

                    $finalAmount = max($originalAmount - $discountAmount, 0);
                }
            }

            // Tạo hóa đơn (invoice)
            $invoice = Invoice::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'amount' => $originalAmount,
                'coupon_code' => $discount ? $discount->code : null,
                'coupon_discount' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => 'Đã thanh toán',
                'code' => Str::random(10),
            ]);

            $courseUser = CourseUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);

            $transaction = Transaction::create([
                'transaction_code' => $inputData['vnp_TxnRef'],
                'user_id' => $userId,
                'amount' => $inputData['vnp_Amount'] / 100,
                'transactionable_id' => $invoice->id,
                'transactionable_type' => Invoice::class,
                'status' => 'Giao dịch thành công',
                'type' => 'invoice',
            ]);

            $this->finalBuyCourse($userId, $course, $transaction, $invoice, $discount, $finalAmount);

            DB::commit();

            return redirect()->away($frontendUrl . "?status=success");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->away($frontendUrl . "?status=error");
        }
    }

    public function buyCourse(BuyCourseRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $userID = Auth::id();

            if (!$userID) {
                return $this->respondUnAuthenticated('Vui lòng đăng nhập để mua khóa học');
            }

            $course = Course::query()->where([
                'slug' => $request->slug,
                'status' => 'approved'
            ])->first();

            if (!$course) {
                return $this->respondError('Không tìm thấy khóa học');
            }

            if (CourseUser::where([
                'user_id' => $userID,
                'course_id' => $course->id,
            ])->exists()) {
                return $this->respondError('Bạn đã mua khóa học này rồi');
            }

            $discountAmount = 0;
            $discount = null;

            if (!empty($validated['coupon_code'])) {
                $discount = Coupon::query()->where([
                    'code' => $validated['coupon_code'],
                    'status' => '1'
                ])->first();

                if (!empty($discount)) {
                    $discountAmount = ($discount->discount_type === 'percentage')
                        ? (!empty(round($course->price_sale, 2)) ? $course->price_sale : $course->price) * $discount->discount_value / 100
                        : $discount->discount_value;

                    $discountAmount = min($discountAmount, !empty(round($course->price_sale, 2)) ? $course->price_sale : $course->price);
                } else {
                    return $this->respondError('Mã giảm giá không hợp lệ hoặc đã hết hạn');
                }
            }

            $finalAmount = max($validated['amount'] - $discountAmount, 0);

            if ($finalAmount === 0) {
                $invoice = Invoice::create([
                    'user_id' => $userID,
                    'course_id' => $course->id,
                    'code' => 'HD' . strtoupper(Str::random(8)),
                    'coupon_code' => $validated['coupon_code'] ?? null,
                    'coupon_discount' => $discountAmount > 0 ? $discountAmount : null,
                    'amount' => $validated['amount'],
                    'final_amount' => $finalAmount,
                    'status' => 'Đã thanh toán',
                ]);

                $transaction = Transaction::create([
                    'transaction_code' => 'GD' . strtoupper(Str::random(8)),
                    'user_id' => $userID,
                    'amount' => $validated['amount'],
                    'transactionable_id' => $invoice->id,
                    'transactionable_type' => Invoice::class,
                    'status' => 'Giao dịch thành công',
                    'type' => 'invoice',
                ]);

                CourseUser::create([
                    'user_id' => $userID,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                ]);

                $this->finalBuyCourse($userID, $course, $transaction, $invoice, $discount);

                DB::commit();

                return $this->respondOk('Mua khóa học thành công');
            } else {
                DB::commit();
                $payment_method = !empty($request->payment_method) ? $request->payment_method : 'vnpay';

                if ($payment_method === 'bank') {
                    return $this->respondOk('Chưa có bank');
                } else {
                    $modifiedRequest = $request->merge([
                        'course_id' => $course->id
                    ]);

                    return $this->createVNPayPayment($modifiedRequest);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    private function finalBuyCourse($userID, $course, $transaction, $invoice, $discount = null, $finalAmount = null)
    {
        if ($discount) {
            $discount->refresh();
            $discount->increment('used_count');

            $couponUse = CouponUse::query()->where([
                'coupon_id' => $discount->id,
                'user_id' => $userID
            ]);

            $couponUse->update([
                'status' => 'used',
            ]);
        }

        $course->refresh();
        $course->increment('total_student');

        $walletInstructor = Wallet::query()
            ->firstOrCreate([
                'user_id' => $course->user_id
            ]);

        $walletInstructor->balance += $finalAmount * self::instructorRate;

        $walletInstructor->save();

        $walletWeb = Wallet::query()
            ->firstOrCreate([
                'user_id' => User::where('email', self::walletMail)
                    ->value('id'),
            ]);

        $walletWeb->balance += $finalAmount * self::adminRate;
        $walletWeb->save();

        SystemFund::query()->updateOrCreate([
            ['id' => 1],
            [
                'balance' => $finalAmount * self::adminRate,
                'pending_balance' => $finalAmount * self::instructorRate
            ]
        ]);

        SystemFundTransaction::query()->create([
            'transaction_id' => $transaction->id,
            'course_id' => $course->id,
            'user_id' => $userID,
            'total_amount' => $finalAmount,
            'retained_amount' => $finalAmount * self::adminRate,
            'type' => 'commission_received',
            'description' => 'Tiền hoa hồng nhận được từ việc bán khóa học: ' . $course->name,
        ]);

        $instructor = $course->user;

        User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })
            ->each(fn($manager) => $manager->notify(
                new UserBuyCourseNotification(User::find($userID), $course->load('invoices.transaction'))
            ));

        $instructor->notify(
            new InstructorNotificationForCoursePurchase(
                User::find($userID),
                $course,
                $transaction
            )
        );

        $student = User::find($userID);

        Mail::to($student->email)->send(
            new StudentCoursePurchaseMail($student, $course, $transaction, $invoice)
        );
    }

    public function applyCoupon(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $data = $request->validate([
                'code' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'course_id' => 'nullable|integer|exists:courses,id',
            ]);

            return $this->checkCoupon($data['code'], $data['amount'], $data['course_id']);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    private function checkCoupon(?string $code, float $amount, $courseId = null)
    {
        if (empty($code)) {
            return [
                'original_amount' => $amount,
                'discount_amount' => 0,
                'final_amount' => $amount,
            ];
        }

        $coupon = Coupon::query()->where('code', $code)->where('status', '1')->first();

        if (!$coupon) {
            return $this->respondNotFound('Mã giảm giá không hợp lệ');
        }

        $alreadyUsed = CouponUse::query()
            ->where('user_id', Auth::id())
            ->where('coupon_id', $coupon->id)
            ->where('status', 'used')
            ->exists();

        if ($alreadyUsed) {
            return $this->respondError('Bạn đã sử dụng mã giảm giá này');
        }

        if (!is_null($coupon->max_usage) && $coupon->used_count >= $coupon->max_usage) {
            return $this->respondError('Mã giảm giá đã hết số lượt sử dụng');
        }

        if ($coupon->start_date && now()->lessThan($coupon->start_date)) {
            return $this->respondError('Mã giảm giá chưa được kích hoạt');
        }

        if ($coupon->specific_course) {
            if (is_null($courseId)) {
                return $this->respondError('Mã giảm giá này chỉ áp dụng cho khóa học cụ thể. Vui lòng cung cấp ID khóa học');
            }

            $isApplicable = $coupon
                ->couponCourses()
                ->where('course_id', $courseId)->exists();
            if (!$isApplicable) {
                return $this->respondError('Mã giảm giá này không áp dụng cho khóa học này');
            }
        }

        $discountAmount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($amount * $coupon->discount_value) / 100;

            if (!empty($coupon->discount_max_value)) {
                $discountAmount = min($discountAmount, $coupon->discount_max_value);
            }
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($coupon->discount_value, $amount);
        }

        $finalAmount = max($amount - $discountAmount, 0);

        return $this->respondOk('Áp dụng mã giảm giá thành công', [
            'original_amount' => $amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount
        ]);
    }

    private function checkCouponNoJson(?string $code, float $amount, $courseId = null): array
    {
        if (empty($code)) {
            return [
                'success' => true,
                'data' => [
                    'original_amount' => $amount,
                    'discount_amount' => 0,
                    'final_amount' => $amount,
                ],
            ];
        }

        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('status', '1')
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'error' => 'Mã giảm giá không hợp lệ',
            ];
        }

        $alreadyUsed = CouponUse::query()
            ->where('user_id', Auth::id())
            ->where('coupon_id', $coupon->id)
            ->where('status', 'used')
            ->exists();

        if ($alreadyUsed) {
            return [
                'success' => false,
                'error' => 'Bạn đã sử dụng mã giảm giá này',
            ];
        }

        if (!is_null($coupon->max_usage) && $coupon->used_count >= $coupon->max_usage) {
            return [
                'success' => false,
                'error' => 'Mã giảm giá đã hết số lượt sử dụng',
            ];
        }

        if ($coupon->start_date && now()->lessThan($coupon->start_date)) {
            return [
                'success' => false,
                'error' => 'Mã giảm giá chưa được kích hoạt',
            ];
        }

        if ($coupon->specific_course) {
            if (is_null($courseId)) {
                return [
                    'success' => false,
                    'error' => 'Mã giảm giá này chỉ áp dụng cho khóa học cụ thể. Vui lòng cung cấp ID khóa học',
                ];
            }

            $isApplicableToCourse = $coupon->couponCourses()
                ->where('course_id', $courseId)
                ->exists();

            if (!$isApplicableToCourse) {
                return [
                    'success' => false,
                    'error' => 'Mã giảm giá này không áp dụng cho khóa học này',
                ];
            }
        }

        $discountAmount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($amount * $coupon->discount_value) / 100;

            if (!empty($coupon->discount_max_value)) {
                $discountAmount = min($discountAmount, $coupon->discount_max_value);
            }
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($coupon->discount_value, $amount);
        }

        $finalAmount = max($amount - $discountAmount, 0);

        return [
            'success' => true,
            'data' => [
                'original_amount' => $amount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
            ],
        ];
    }
}
