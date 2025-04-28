<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\BuyCourseRequest;
use App\Http\Requests\API\Transaction\DepositTransactionRequest;
use App\Mail\MembershipPurchaseMail;
use App\Mail\StudentCoursePurchaseMail;
use App\Models\Conversation;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\InstructorCommission;
use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\MembershipSubscription;
use App\Models\Spin;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\InstructorNotificationForCoursePurchase;
use App\Notifications\InstructorNotificationForMembershipPurchase;
use App\Notifications\JoiFreeCourseNotification;
use App\Notifications\SpinReceivedNotification;
use App\Notifications\UserBuyCourseNotification;
use App\Notifications\UserBuyMembershipNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Return_;

class TransactionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    const walletMail = 'coursemely@gmail.com';

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
                'source' => 'free',
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

    public function createPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Vui lòng đăng nhập để thực hiện thanh toán');
            }

            $validated = $request->validate([
                'amount' => 'required|numeric',
                'payment_type' => 'required|in:course,membership',
                'item_id' => 'required|integer',
                'coupon_code' => 'nullable|string',
                'payment_method' => 'required|string',
                'original_amount' => 'required|numeric',
            ]);

            $paymentType = $validated['payment_type'];
            $itemId = $validated['item_id'];
            $finalAmount = $validated['amount'];

            if ($paymentType === 'course') {
                $existingPurchase = CourseUser::query()
                    ->lockForUpdate()
                    ->where([
                        'user_id' => $user->id,
                        'course_id' => $itemId,
                        'source' => 'purchase'
                    ])
                    ->exists();

                if ($existingPurchase) {
                    DB::rollBack();
                    return $this->respondError('Bạn đã sở hữu khoá học này rồi');
                }

                $item = Course::query()
                    ->lockForUpdate()
                    ->find($itemId);

                if (!$item) {
                    DB::rollBack();
                    return $this->respondError('Không tìm thấy khóa học');
                }

                $originalAmount = $item->price_sale > 0 ? $item->price_sale : $item->price;

                $currentPrice = $item->price_sale > 0 ? $item->price_sale : $item->price;
                $submittedOriginalPrice = $validated['original_amount'];

                if ($currentPrice != $submittedOriginalPrice) {
                    DB::rollBack();
                    return $this->respondError('Giá khóa học đã thay đổi. Vui lòng tải lại trang để xem giá mới nhất.');
                }
            } else {

                $existingMembershipSubscription = MembershipSubscription::query()
                    ->lockForUpdate()
                    ->where('user_id', $user->id)
                    ->where('membership_plan_id', $itemId)
                    ->where('end_date', '>', now())
                    ->exists();

                if ($existingMembershipSubscription) {
                    DB::rollBack();
                    return $this->respondError('Bạn đã đăng ký gói membership này rồi');
                }

                $item = MembershipPlan::query()
                    ->lockForUpdate()
                    ->find($itemId);

                if (!$item) {
                    DB::rollBack();
                    return $this->respondError('Không tìm thấy gói membership');
                }

                $originalAmount = $item->price;

                $currentPrice = $item->price;
                $submittedOriginalPrice = $validated['original_amount'];

                if ($currentPrice != $submittedOriginalPrice) {
                    DB::rollBack();
                    return $this->respondError('Giá gói membership đã thay đổi. Vui lòng tải lại trang để xem giá mới nhất.');
                }
            }

            $discountAmount = $originalAmount - $finalAmount;
            $couponCode = $validated['coupon_code'] ?? null;

            $result = match ($validated['payment_method']) {
                'momo' => $this->createMoMoPayment($user, $itemId, $originalAmount, $discountAmount, $finalAmount, $couponCode, $paymentType),
                'vnpay' => $this->createVnPayPayment($user, $itemId, $originalAmount, $discountAmount, $finalAmount, $couponCode, $paymentType),
                default => $this->respondError('Phương thức thanh toán không hợp lệ')
            };

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    protected function createVnPayPayment($user, $itemId, $originalAmount, $discountAmount, $finalAmount, $couponCode = null, $paymentType = 'course')
    {
        $amountVNPay = number_format($finalAmount, 0, '', '');

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_ReturnUrl = config('vnpay.return_url');

        $vnp_TxnRef = 'ORDER' . time();
        $vnp_OrderInfo = $user->id . '-Thanh-toan-' . $paymentType . '-' . $itemId .
            '-' . $originalAmount . '-' . $discountAmount . '-' . $finalAmount;

        if (!empty($couponCode)) {
            $vnp_OrderInfo .= '-' . $couponCode;
        }

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amountVNPay * 100,
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0;

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
    }

    protected function vnpayCallback(Request $request)
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

            if ($inputData['vnp_ResponseCode'] != '00') {
                return redirect()->away($frontendUrl . "?status=failed");
            }

            DB::beginTransaction();

            if (!isset($inputData['vnp_OrderInfo'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $orderParts = explode('-Thanh-toan-', $inputData['vnp_OrderInfo']);
            if (count($orderParts) != 2) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $userId = filter_var(trim($orderParts[0], '"'), FILTER_VALIDATE_INT);

            $remainingInfo = $orderParts[1];
            $typeParts = explode('-', $remainingInfo);
            $paymentType = $typeParts[0];

            $itemIdStartPos = strpos($remainingInfo, '-') + 1;
            $remainingParts = explode('-', substr($remainingInfo, $itemIdStartPos));

            $itemId = filter_var(trim($remainingParts[0], '"'), FILTER_VALIDATE_INT);
            $originalAmount = filter_var(trim($remainingParts[1], '"'), FILTER_VALIDATE_FLOAT);
            $discountAmount = filter_var(trim($remainingParts[2], '"'), FILTER_VALIDATE_FLOAT);
            $finalAmount = filter_var(trim($remainingParts[3], '"'), FILTER_VALIDATE_FLOAT);
            $couponCode = count($remainingParts) > 4 ? trim($remainingParts[4], '"') : null;

            $user = User::query()->find($userId);

            if (!$user) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            if ($paymentType === 'course') {
                $course = Course::query()
                    ->lockForUpdate()
                    ->find($itemId);

                if (!$course) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "/not-found");
                }

                $currentPrice = $course->price_sale > 0 ? $course->price_sale : $course->price;

                if ($currentPrice != $originalAmount) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "?status=error");
                }

                $existingPurchase = CourseUser::query()
                    ->lockForUpdate()
                    ->where([
                        'user_id' => $userId,
                        'course_id' => $itemId,
                        'source' => 'purchase'
                    ])
                    ->exists();

                if ($existingPurchase) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "?status=error");
                }

                $discount = null;
                if (!empty($couponCode)) {
                    $discount = Coupon::query()
                        ->where(['code' => $couponCode, 'status' => '1'])
                        ->lockForUpdate()
                        ->first();

                    if (!$discount) {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }

                    if (!is_null($discount->max_usage) && $discount->used_count >= $discount->max_usage) {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }

                    $couponUse = CouponUse::query()
                        ->where('coupon_id', $discount->id)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if ($couponUse && $couponUse->status === 'used') {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }
                }

                $instructorCommissions = InstructorCommission::query()
                    ->select('rate')
                    ->lockForUpdate()
                    ->where('instructor_id', $course->user_id)->first();

                $instructorCommissions = !empty($instructorCommissions) ? $instructorCommissions->rate : 0.6;

                $invoice = Invoice::create([
                    'user_id' => $userId,
                    'course_id' => $itemId,
                    'membership_plan_id' => null,
                    'amount' => $originalAmount,
                    'coupon_code' => $discount ? $discount->code : null,
                    'coupon_discount' => $discountAmount,
                    'final_amount' => $finalAmount,
                    'status' => 'Đã thanh toán',
                    'instructor_commissions' => $instructorCommissions,
                    'code' => 'INV' . Str::upper(Str::random(10)),
                    'payment_method' => 'vnpay',
                    'invoice_type' => 'course',
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

                $this->finalBuyCourse($userId, $course, $transaction, $invoice, $discount, $finalAmount, $instructorCommissions);

                DB::commit();

                return redirect()->away($frontendUrl . "?status=success");
            } else {
                $memberShipPlan = MembershipPlan::query()->find($itemId);

                if (!$memberShipPlan) {
                    return redirect()->away($frontendUrl . "/not-found");
                }

                $instructorCommissions = InstructorCommission::query()
                    ->select('rate')
                    ->lockForUpdate()
                    ->where('instructor_id', $memberShipPlan->instructor_id)->first();

                $instructorCommissions = !empty($instructorCommissions) ? $instructorCommissions->rate : 0.6;

                $invoice = Invoice::query()->create([
                    'user_id' => $userId,
                    'course_id' => null,
                    'membership_plan_id' => $itemId,
                    'amount' => $originalAmount,
                    'final_amount' => $finalAmount,
                    'status' => 'Đã thanh toán',
                    'instructor_commissions' => $instructorCommissions,
                    'code' => 'INV' . Str::upper(Str::random(10)),
                    'payment_method' => 'vnpay',
                    'invoice_type' => 'membership'
                ]);

                $duration = $memberShipPlan->duration_months;

                $courses = $memberShipPlan->membershipCourseAccess()->pluck('course_id')->toArray();

                $existingMembership = MembershipSubscription::query()
                    ->where('user_id', $userId)
                    ->where('membership_plan_id', $itemId)
                    ->first();

                if ($existingMembership && $existingMembership->status === 'expired') {
                    $oldEndDate = $existingMembership->end_date;

                    if ($existingMembership->end_date > now()) {
                        $newEndDate = Carbon::parse($existingMembership->end_date)->addMonths($duration);
                    } else {
                        $newEndDate = now()->addMonths($duration);
                    }

                    $existingMembership->update([
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => $newEndDate
                    ]);

                    $existingMembership->addLog(
                        'renewed',
                        'Đã gia hạn gói thành viên',
                        [
                            'old_end_date' => $oldEndDate->toDateTimeString(),
                            'new_end_date' => $newEndDate->toDateTimeString(),
                            'transaction_id' => $inputData['vnp_TxnRef'],
                            'amount' => $inputData['vnp_Amount'] / 100,
                            'invoice_id' => $invoice->id
                        ]
                    );
                } else {
                    $newEndDate = now()->addMonths($duration);

                    MembershipSubscription::query()->create([
                        'membership_plan_id' => $itemId,
                        'user_id' => $userId,
                        'start_date' => now(),
                        'end_date' => $newEndDate,
                        'status' => 'active',
                        'activity_logs' => [
                            [
                                'action' => 'created',
                                'details' => 'Đã mua gói thành viên',
                                'data' => [
                                    'start_date' => now()->toDateTimeString(),
                                    'end_date' => $newEndDate->toDateTimeString(),
                                    'transaction_id' => $inputData['vnp_TxnRef'],
                                    'amount' => $inputData['vnp_Amount'] / 100,
                                    'invoice_id' => $invoice->id
                                ],
                                'timestamp' => now()->toDateTimeString(),
                            ]
                        ]
                    ]);
                }

                $transaction = Transaction::query()->create([
                    'transaction_code' => 'TXN' . $inputData['vnp_TxnRef'],
                    'user_id' => $userId,
                    'amount' => $inputData['vnp_Amount'] / 100,
                    'transactionable_id' => $invoice->id,
                    'transactionable_type' => Invoice::class,
                    'status' => 'Giao dịch thành công',
                    'type' => 'invoice',
                ]);

                foreach ($courses as $courseId) {
                    CourseUser::query()->updateOrCreate(
                        [
                            'user_id' => $userId,
                            'course_id' => $courseId
                        ],
                        [
                            'enrolled_at' => now(),
                            'access_status' => 'active',
                            'source' => 'membership'
                        ]
                    );
                }

                CourseUser::query()
                    ->where('user_id', $userId)
                    ->where('source', 'membership')
                    ->where('access_status', '!=', 'active')
                    ->update(['access_status' => 'active']);

                $this->finalBuyMembership(
                    $userId,
                    $memberShipPlan,
                    $transaction,
                    $invoice,
                    $finalAmount,
                    $instructorCommissions
                );

                $student = User::query()->find($userId);

                Mail::to($student->email)->send(
                    new MembershipPurchaseMail($student, $memberShipPlan, $transaction, $invoice)
                );

                DB::commit();

                return redirect()->away($frontendUrl . "?status=success");
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away($frontendUrl . "?status=error");
        }
    }

    protected function createMoMoPayment($user, $itemId, $originalAmount, $discountAmount, $finalAmount, $couponCode = null, $paymentType = 'course')
    {
        try {
            $endpoint = config('momo.endpoint');
            $partnerCode = config('momo.partner_code');
            $accessKey = config('momo.access_key');
            $secretKey = config('momo.secret_key');
            $returnUrl = config('momo.return_url');
            $ipnUrl = config('momo.notify_url', $returnUrl);
            $requestType = "payWithCC";
            $extraData = "";

            $orderId = 'ORDER' . time();
            $requestId = time() . "_" . uniqid();

            $orderInfo = $user->id . '-Thanh-toan-' . $paymentType . '-' . $itemId .
                '-' . $originalAmount . '-' . $discountAmount . '-' . $finalAmount;

            if (!empty($couponCode)) {
                $orderInfo .= '-' . $couponCode;
            }

            $finalAmount = (float)$finalAmount;

            $rawHash =
                "accessKey=" . $accessKey .
                "&amount=" . $finalAmount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $returnUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'requestId' => $requestId,
                'amount' => $finalAmount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $returnUrl,
                'ipnUrl' => $ipnUrl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            ];

            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            if (isset($jsonResult['payUrl'])) {
                return $this->respondOk('Tạo link thanh toán MoMo thành công', $jsonResult['payUrl']);
            } else {
                return $this->respondServerError('Ngân hàng tạm thời bảo trì: ' . ($jsonResult['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra khi tạo thanh toán MoMo: ' . $e->getMessage());
        }
    }

    protected function momoCallback(Request $request)
    {
        try {
            $inputData = $request->all();
            $frontendUrl = config('app.fe_url') . "/payment";
            if (!isset($inputData['signature'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            if ($inputData['resultCode'] != '0') {
                return redirect()->away($frontendUrl . "?status=failed");
            }

            DB::beginTransaction();

            if (!isset($inputData['orderInfo'])) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $orderParts = explode('-Thanh-toan-', $inputData['orderInfo']);
            if (count($orderParts) != 2) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $userId = filter_var(trim($orderParts[0], '"'), FILTER_VALIDATE_INT);

            $remainingInfo = $orderParts[1];
            $typeParts = explode('-', $remainingInfo);
            $paymentType = $typeParts[0];

            $itemIdStartPos = strpos($remainingInfo, '-') + 1;
            $remainingParts = explode('-', substr($remainingInfo, $itemIdStartPos));

            if (count($remainingParts) < 4) {
                return redirect()->away($frontendUrl . "?status=error");
            }

            $itemId = filter_var(trim($remainingParts[0], '"'), FILTER_VALIDATE_INT);
            $originalAmount = filter_var(trim($remainingParts[1], '"'), FILTER_VALIDATE_FLOAT);
            $discountAmount = filter_var(trim($remainingParts[2], '"'), FILTER_VALIDATE_FLOAT);
            $finalAmount = filter_var(trim($remainingParts[3], '"'), FILTER_VALIDATE_FLOAT);
            $couponCode = count($remainingParts) > 4 ? trim($remainingParts[4], '"') : null;

            $user = User::query()->find($userId);
            if (!$user) {
                return redirect()->away($frontendUrl . "/not-found");
            }

            if ($paymentType === 'course') {
                $course = Course::query()
                    ->lockForUpdate()
                    ->find($itemId);

                if (!$course) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "/not-found");
                }

                $currentPrice = $course->price_sale > 0 ? $course->price_sale : $course->price;

                if ($currentPrice != $originalAmount) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "?status=error");
                }

                $existingPurchase = CourseUser::query()
                    ->lockForUpdate()
                    ->where([
                        'user_id' => $userId,
                        'course_id' => $itemId,
                        'source' => 'purchase'
                    ])
                    ->exists();

                if ($existingPurchase) {
                    DB::rollBack();
                    return redirect()->away($frontendUrl . "?status=error");
                }

                $discount = null;
                if (!empty($couponCode)) {
                    $discount = Coupon::query()
                        ->where(['code' => $couponCode, 'status' => '1'])
                        ->lockForUpdate()
                        ->first();

                    if (!$discount) {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }

                    if (!is_null($discount->max_usage) && $discount->used_count >= $discount->max_usage) {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }

                    $couponUse = CouponUse::query()
                        ->where('coupon_id', $discount->id)
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if ($couponUse && $couponUse->status === 'used') {
                        DB::rollBack();
                        return redirect()->away($frontendUrl . "?status=error");
                    }
                }

                $instructorCommissions = InstructorCommission::query()
                    ->select('rate')
                    ->lockForUpdate()
                    ->where('instructor_id', $course->user_id)->first();

                $instructorCommissions = !empty($instructorCommissions) ? $instructorCommissions->rate : 0.6;

                $invoice = Invoice::create([
                    'user_id' => $userId,
                    'course_id' => $itemId,
                    'membership_plan_id' => null,
                    'amount' => $originalAmount,
                    'coupon_code' => $discount ? $discount->code : null,
                    'coupon_discount' => $discountAmount,
                    'final_amount' => $finalAmount,
                    'status' => 'Đã thanh toán',
                    'instructor_commissions' => $instructorCommissions,
                    'code' => 'INV' . Str::upper(Str::random(10)),
                    'payment_method' => 'momo',
                    'payment_type' => 'course'
                ]);

                $transaction = Transaction::create([
                    'transaction_code' => 'TXN' . $inputData['orderId'],
                    'user_id' => $userId,
                    'amount' => $inputData['amount'],
                    'transactionable_id' => $invoice->id,
                    'transactionable_type' => Invoice::class,
                    'status' => 'Giao dịch thành công',
                    'type' => 'invoice',
                ]);

                $this->finalBuyCourse($userId, $course, $transaction, $invoice, $discount, $finalAmount, $instructorCommissions);

                DB::commit();

                return redirect()->away($frontendUrl . "?status=success");
            } else {
                $memberShipPlan = MembershipPlan::query()->find($itemId);

                if (!$memberShipPlan) {
                    return redirect()->away($frontendUrl . "/not-found");
                }

                $instructorCommissions = InstructorCommission::query()
                    ->select('rate')
                    ->lockForUpdate()
                    ->where('instructor_id', $memberShipPlan->instructor_id)->first();

                $instructorCommissions = !empty($instructorCommissions) ? $instructorCommissions->rate : 0.6;

                $invoice = Invoice::query()->create([
                    'user_id' => $userId,
                    'course_id' => null,
                    'membership_plan_id' => $itemId,
                    'amount' => $originalAmount,
                    'final_amount' => $finalAmount,
                    'status' => 'Đã thanh toán',
                    'instructor_commissions' => $instructorCommissions,
                    'code' => 'INV' . Str::upper(Str::random(10)),
                    'payment_method' => 'vnpay',
                    'invoice_type' => 'membership'
                ]);

                $duration = $memberShipPlan->duration_months;

                $courses = $memberShipPlan->membershipCourseAccess()->pluck('course_id')->toArray();

                $existingMembership = MembershipSubscription::query()
                    ->where('user_id', $userId)
                    ->where('membership_plan_id', $itemId)
                    ->first();

                if ($existingMembership && $existingMembership->status === 'expired') {
                    $oldEndDate = $existingMembership->end_date;

                    if ($existingMembership->end_date > now()) {
                        $newEndDate = Carbon::parse($existingMembership->end_date)->addMonths($duration);
                    } else {
                        $newEndDate = now()->addMonths($duration);
                    }

                    $existingMembership->update([
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => $newEndDate
                    ]);

                    $existingMembership->addLog(
                        'renewed',
                        'Đã gia hạn gói thành viên',
                        [
                            'old_end_date' => $oldEndDate->toDateTimeString(),
                            'new_end_date' => $newEndDate->toDateTimeString(),
                            'transaction_id' =>  $inputData['orderId'],
                            'amount' => $inputData['amount'] / 100,
                            'invoice_id' => $invoice->id
                        ]
                    );
                } else {
                    $newEndDate = now()->addMonths($duration);

                    MembershipSubscription::query()->create([
                        'membership_plan_id' => $itemId,
                        'user_id' => $userId,
                        'start_date' => now(),
                        'end_date' => $newEndDate,
                        'status' => 'active',
                        'activity_logs' => [
                            [
                                'action' => 'created',
                                'details' => 'Đã mua gói thành viên',
                                'data' => [
                                    'start_date' => now()->toDateTimeString(),
                                    'end_date' => $newEndDate->toDateTimeString(),
                                    'transaction_id' => $inputData['orderId'],
                                    'amount' => $inputData['amount'],
                                    'invoice_id' => $invoice->id
                                ],
                                'timestamp' => now()->toDateTimeString(),
                            ]
                        ]
                    ]);
                }

                $transaction = Transaction::query()->create([
                    'transaction_code' => 'TXN' . $inputData['orderId'],
                    'user_id' => $userId,
                    'amount' => $inputData['amount'],
                    'transactionable_id' => $invoice->id,
                    'transactionable_type' => Invoice::class,
                    'status' => 'Giao dịch thành công',
                    'type' => 'invoice',
                ]);

                foreach ($courses as $courseId) {
                    CourseUser::query()->updateOrCreate(
                        [
                            'user_id' => $userId,
                            'course_id' => $courseId
                        ],
                        [
                            'enrolled_at' => now(),
                            'access_status' => 'active',
                            'source' => 'membership'
                        ]
                    );
                }

                CourseUser::query()
                    ->where('user_id', $userId)
                    ->where('source', 'membership')
                    ->where('access_status', '!=', 'active')
                    ->update(['access_status' => 'active']);

                $spin =  Spin::query()->create([
                    'user_id' => $userId,
                    'spin_count' => 1,
                    'received_at' => now(),
                    'expires_at' => now()->addDays(7)
                ]);

                $user->notify(new SpinReceivedNotification($user->id, $spin->spin_count, $spin->expires_at));

                $this->finalBuyMembership(
                    $userId,
                    $memberShipPlan,
                    $transaction,
                    $invoice,
                    $finalAmount,
                    $instructorCommissions
                );

                $student = User::query()->find($userId);

                Mail::to($student->email)->send(
                    new MembershipPurchaseMail($student, $memberShipPlan, $transaction, $invoice)
                );

                DB::commit();

                return redirect()->away($frontendUrl . "?status=success");
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->away($frontendUrl . "?status=error");
        }
    }

    private function finalBuyCourse($userID, $course, $transaction, $invoice, $discount = null, $finalAmount = null, $instructorCommissions)
    {
        DB::transaction(function () use ($userID, $course, $transaction, $invoice, $discount, $finalAmount, $instructorCommissions) {

            if ($discount) {
                $discount->refresh();
                $discount->increment('used_count');

                if ($discount->max_usage > 0) {
                    $discount->decrement('max_usage');
                }

                $couponUse = CouponUse::query()->where([
                    'coupon_id' => $discount->id,
                    'user_id' => $userID
                ])->lockForUpdate();

                $couponUse->update([
                    'status' => 'used',
                    'applied_at' => now(),
                ]);

                $this->clearCouponCache($userID, $discount->code);
            }

            $conversation = Conversation::query()->where([
                'conversationable_id' => $course->id,
                'conversationable_type' => Course::class
            ])->first();

            if ($conversation) {
                $conversation->users()->syncWithoutDetaching([$userID]);
            }

            $course->refresh();
            $course->increment('total_student');

            $existingCourseUser = CourseUser::query()->where([
                'user_id' => $userID,
                'course_id' => $course->id,
                'source' => 'membership'
            ])->first();

            if ($existingCourseUser) {
                $existingCourseUser->update([
                    'source' => 'purchase',
                    'enrolled_at' => now(),
                    'access_status' => 'active'
                ]);
            } else {
                CourseUser::create([
                    'user_id' => $userID,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                    'source' => 'purchase',
                ]);
            }

            $walletInstructor = Wallet::query()
                ->firstOrCreate([
                    'user_id' => $course->user_id
                ]);

            $walletInstructor->balance += $finalAmount * $instructorCommissions;

            $walletInstructor->save();

            $walletWeb = Wallet::query()
                ->firstOrCreate([
                    'user_id' => User::where('email', self::walletMail)
                        ->value('id'),
                ]);

            $walletWeb->balance += $finalAmount * (1 - $instructorCommissions);
            $walletWeb->save();

            $systemFund = SystemFund::query()->first();

            if ($systemFund) {
                $systemFund->balance += $finalAmount * (1 - $instructorCommissions);
                $systemFund->pending_balance += $finalAmount * $instructorCommissions;
                $systemFund->save();
            } else {
                SystemFund::query()->create([
                    'balance' => $finalAmount * (1 - $instructorCommissions),
                    'pending_balance' => $finalAmount * $instructorCommissions
                ]);
            }

            SystemFundTransaction::query()->create([
                'transaction_id' => $transaction->id,
                'user_id' => $userID,
                'total_amount' => $finalAmount,
                'retained_amount' => $finalAmount * (1 - $instructorCommissions),
                'type' => 'commission_received',
                'description' => 'Tiền hoa hồng nhận được từ việc bán khóa học: ' . $course->name,
            ]);

            $instructor = $course->user;

            User::query()->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
                ->each(fn($manager) => $manager->notify(
                    new UserBuyCourseNotification(User::find($userID), $course->load('invoices.transaction'))
                ));

            $instructor->notify(
                new InstructorNotificationForCoursePurchase(
                    User::query()->find($userID),
                    $course,
                    $transaction
                )
            );

            $student = User::query()->find($userID);
            if ($finalAmount > 500000) {
                $spin = Spin::query()->create([
                    'user_id' => $student->id,
                    'spin_count' => 1,
                    'received_at' => now(),
                    'expires_at' => now()->addDays(7),
                ]);

                $student->notify(new SpinReceivedNotification($student->id, $spin->spin_count, $spin->expires_at));
            }

            Mail::to($student->email)->send(
                new StudentCoursePurchaseMail($student, $course, $transaction, $invoice)
            );
        });
    }

    private function finalBuyMembership($userId, $memberShipPlan, $transaction, $invoice, $finalAmount, $instructorCommissions)
    {
        $memberShipPlan->refresh();

        $walletInstructor = Wallet::query()
            ->firstOrCreate([
                'user_id' => $memberShipPlan->instructor_id
            ]);

        $walletInstructor->balance += $finalAmount * $instructorCommissions;
        $walletInstructor->save();

        $walletWeb = Wallet::query()
            ->firstOrCreate([
                'user_id' => User::query()->where('email', self::walletMail)
                    ->value('id'),
            ]);

        $walletWeb->balance += $finalAmount;
        $walletWeb->save();

        $systemFund = SystemFund::query()->first();

        if ($systemFund) {
            $systemFund->balance += $finalAmount * (1 - $instructorCommissions);
            $systemFund->pending_balance += $finalAmount * $instructorCommissions;
            $systemFund->save();
        } else {
            SystemFund::query()->create([
                'balance' => $finalAmount * (1 - $instructorCommissions),
                'pending_balance' => $finalAmount * $instructorCommissions
            ]);
        }

        SystemFundTransaction::query()->create([
            'transaction_id' => $transaction->id,
            'user_id' => $userId,
            'total_amount' => $finalAmount,
            'retained_amount' => $finalAmount * (1 - $instructorCommissions),
            'type' => 'commission_received',
            'description' => 'Phí đăng ký gói membership: ' . $memberShipPlan->name,
        ]);

        $instructor = $memberShipPlan->instructor;

        User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })
            ->each(fn($manager) => $manager->notify(
                new UserBuyMembershipNotification(User::query()->find($userId), $memberShipPlan->load('invoices.transaction'))
            ));

        $instructor->notify(
            new InstructorNotificationForMembershipPurchase(
                User::query()->find($userId),
                $memberShipPlan,
                $transaction
            )
        );

        $spin = Spin::query()->create([
            'user_id' => $userId,
            'spin_count' => 1,
            'received_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        $user = User::query()->find($userId);
        $user->notify(new SpinReceivedNotification($user->id, $spin->spin_count, $spin->expires_at));
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

    public function deleteApplyCoupon(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->respondForbidden('Bạn không có quyền truy cập');
            }

            $data = $request->validate([
                'code' => 'required|string',
            ]);

            $this->clearCouponCache($user->id, $data['code']);

            return $this->respondOk('Hủy mã giảm giá thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
            return $this->respondServerError();
        }
    }

    private function checkCoupon(string $code, float $amount, ?int $courseId = null)
    {
        $cacheKey = "coupon_lock" . Auth::id() . ":" . $code;
        if (!Cache::add($cacheKey, 'processing', 60)) {
            return $this->respondError('Mã giảm giá đang được sử dụng.');
        }

        $coupon = Coupon::where('code', $code)->where('status', '1')->first();
        if (!$coupon) {
            return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá không hợp lệ');
        }

        $couponAssigned = CouponUse::where('user_id', Auth::id())->where('coupon_id', $coupon->id)->first();
        if (!$couponAssigned || $couponAssigned->status === 'used' || ($couponAssigned->expired_at && now()->greaterThan($couponAssigned->expired_at))) {
            return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá không hợp lệ hoặc đã hết hạn');
        }

        if ($coupon->start_date && now()->lessThan($coupon->start_date)) {
            return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá chưa được kích hoạt');
        }

        if (!is_null($coupon->max_usage) && $coupon->used_count >= $coupon->max_usage) {
            return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá đã hết số lượt sử dụng');
        }

        if ($coupon->specific_course && (!$courseId || !$coupon->couponCourses()->where('course_id', $courseId)->exists())) {
            return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá không áp dụng cho khóa học này');
        }

        return DB::transaction(function () use ($coupon, $couponAssigned, $amount, $cacheKey) {
            $coupon = Coupon::query()
                ->where('code', $coupon->code)
                ->where('status', '1')
                ->lockForUpdate()
                ->first();
            $couponAssigned = CouponUse::query()
                ->where('user_id', Auth::id())
                ->where('coupon_id', $coupon->id)
                ->lockForUpdate()
                ->first();

            if (!$coupon || !$couponAssigned) {
                return $this->invalidateCacheAndRespond($cacheKey, 'Mã giảm giá không hợp lệ hoặc đã được sử dụng');
            }

            $discountAmount = $coupon->discount_type === 'percentage'
                ? min(($amount * $coupon->discount_value) / 100, $coupon->discount_max_value ?? PHP_INT_MAX)
                : min($coupon->discount_value, $amount);

            return $this->respondOk('Áp dụng mã giảm giá thành công', [
                'original_amount' => $amount,
                'discount_amount' => $discountAmount,
                'final_amount' => max($amount - $discountAmount, 0),
                'ttl' => 60000,
            ]);
        });
    }

    private function invalidateCacheAndRespond(string $cacheKey, string $message)
    {
        Cache::forget($cacheKey);
        return $this->respondError($message);
    }

    private function clearCouponCache($userId, $couponCode)
    {
        if ($userId && $couponCode) {
            $cacheKey = "coupon_lock" . $userId . ":" . $couponCode;
            Cache::forget($cacheKey);
        }
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
