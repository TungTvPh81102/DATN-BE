<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'membership_plan_id',
        'code',
        'coupon_code',
        'coupon_discount',
        'amount',
        'final_amount',
        'status',
        'payment_method',
        'invoice_type',
        'instructor_commissions'
    ];

    protected $attributes = [
        'status' => 'Chờ xử lý'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class)->with('instructor');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class)->with('instructor');
    }
}
