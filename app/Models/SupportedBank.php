<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportedBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'bin',
        'short_name',
        'logo',
    ];
}
