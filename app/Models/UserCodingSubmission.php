<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCodingSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coding_id',
        'code',
    ];

    public function coding()
    {
        return $this->belongsTo(Coding::class);
    }
}
