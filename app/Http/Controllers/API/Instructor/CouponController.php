<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Coupons\StoreCouponRequest;
use App\Http\Requests\API\Coupons\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\User;
use App\Notifications\CouponSendStudentNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $query = Coupon::query()->where('user_id', $user->id);

            if ($request->has('fromDate')) {
                $query->whereDate('start_date', '>=', $request->input('fromDate'));
            }
            if ($request->has('toDate')) {
                $query->whereDate('start_date', '<=', $request->input('toDate'));
            }

            $coupons = $query->latest()->get();

            if ($coupons->isEmpty()) {
                return $this->respondNotFound('Không có mã giảm giá nào!');
            }

            return $this->respondOk('Danh sách mã giảm giá', $coupons);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $data = $request->validated();

            $data['user_id'] = $user->id;
            $data['start_date'] = $data['start_date'] ?? now();
            $data['max_usage'] = $data['max_usage'] === 0 ? null : $data['max_usage'];

            $coupon = Coupon::query()->create($data);

            if (!empty($data['specific_course'] === 1) && !empty($data['course_ids']) && is_array($data['course_ids'])) {
                foreach ($data['course_ids'] as $course_id) {
                    DB::table('coupon_course')->insert([
                        'coupon_id' => $coupon->id,
                        'course_id' => $course_id,
                    ]);
                }
                $coupon->update([
                    'specific_course' => 1
                ]);
            }

            if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
                foreach ($data['user_ids'] as $user_id) {
                    CouponUse::query()->updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'coupon_id' => $coupon->id
                        ],
                        [
                            'status' => 'unused',
                            'applied_at' => null,
                            'expired_at' => $data['expire_date'] ?? null
                        ]
                    );

                    $notificationMember = User::query()->find($user_id);

                    if ($notificationMember) {
                        $notificationMember->notify(new CouponSendStudentNotification(
                            $user,
                            $notificationMember
                        ));
                    }
                }
            }

            DB::commit();

            return $this->respondCreated('Tạo mã giảm giá thành công', $coupon);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $coupon = Coupon::query()
                ->with([
                    'couponUses.user',
                    'couponCourses:id,name'
                ])
                ->where('user_id', $user->id)
                ->find($id);

            if (!$coupon) {
                return $this->respondNotFound('Không có má giảm giá nào!');
            }

            return $this->respondOk('Thông tin mã giảm giá', $coupon);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $coupon = Coupon::query()
                ->where('user_id', $user->id)
                ->find($id);

            $data = $request->validated();

            if (!$coupon) {
                return $this->respondNotFound('Không có mã giảm giá nào!');
            }

            if (isset($data['discount_type']) && $data['discount_type'] === 'fixed') {
                $data['discount_max_value'] = 0;
            }

            if (isset($data['max_usage']) && $data['max_usage'] === 0) {
                $data['max_usage'] = null;
            }

            if (!empty($data['specific_course']) && !empty($data['course_ids']) && is_array($data['course_ids'])) {
                DB::table('coupon_course')->where('coupon_id', $coupon->id)->delete();

                foreach ($data['course_ids'] as $course_id) {
                    DB::table('coupon_course')->insert([
                        'coupon_id' => $coupon->id,
                        'course_id' => $course_id,
                    ]);
                }
            } else {
                DB::table('coupon_course')->where('coupon_id', $coupon->id)->delete();
            }

            $coupon->update($data);

            if (isset($data['user_ids']) && is_array($data['user_ids'])) {
                if (count($data['user_ids']) === 0) {
                    CouponUse::query()
                        ->where('coupon_id', $coupon->id)
                        ->delete();
                } else {
                    CouponUse::query()
                        ->where('coupon_id', $coupon->id)
                        ->whereNotIn('user_id', $data['user_ids'])
                        ->delete();

                    $existingCouponUses = CouponUse::query()
                        ->where('coupon_id', $coupon->id)
                        ->get()
                        ->keyBy('user_id');

                    foreach ($data['user_ids'] as $user_id) {
                        if ($existingCouponUses->has($user_id)) {
                            $existingUse = $existingCouponUses->get($user_id);

                            if ($existingUse->status === 'unused') {
                                $existingUse->expired_at = $data['expire_date'] ?? null;
                                $existingUse->save();
                            }
                        } else {
                            CouponUse::create([
                                'user_id' => $user_id,
                                'coupon_id' => $coupon->id,
                                'status' => 'unused',
                                'applied_at' => null,
                                'expired_at' => $data['expire_date'] ?? null
                            ]);
                        }
                    }
                }
            } else {
                CouponUse::query()
                    ->where('coupon_id', $coupon->id)
                    ->delete();
            }

            DB::commit();

            return $this->respondOk('Thao tác thành công', $coupon);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function toggleStatus(string $id, string $action)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $coupon = Coupon::query()
                ->where('user_id', $user->id)
                ->find($id);

            if (!$coupon) {
                return $this->respondForbidden('Không có mã giảm giá nào!');
            }

            $status = $action === 'enable' ? '1' : '0';

            $coupon->status = $status;
            $coupon->save();

            $message = $action === 'enable' ? 'Kích hoạt thành công' : 'Vô hiệu hóa thành công';
            return $this->respondOk($message);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->hasRole('instructor')) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
            }

            $coupon = Coupon::query()
                ->where('user_id', $user->id)
                ->findOrFail($id);

            if (!$coupon) {
                return $this->respondNotFound('Không có mã giảm giá nào!');
            }

            $coupon->delete();

            return $this->respondOk('Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}
