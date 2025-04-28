<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStreamCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'stream_key',
        'mux_stream_id',
        'mux_playback_id',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }

    public function liveSessionParticipants()
    {
        return $this->hasMany(LiveSessionParticipant::class);
    }
}
