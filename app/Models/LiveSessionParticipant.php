<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSessionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'live_session_id',
        'role',
        'joined_at',
    ];
}
