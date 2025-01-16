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
        'coupon_code',
        'coupon_discount',
        'total',
        'final_total',
        'status'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];
}
