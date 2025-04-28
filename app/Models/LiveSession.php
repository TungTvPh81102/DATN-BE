<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_credential_id',
        'instructor_id',
        'code',
        'title',
        'thumbnail',
        'description',
        'visibility',
        'status',
        'starts_at',
        'actual_start_time',
        'actual_end_time',
        'recording_asset_id',
        'recording_playback_id',
        'duration',
        'recording_url',
        'viewers_count',
    ];

    public function conversation()
    {
        return $this->morphOne(Conversation::class, 'conversationable');
    }

    public function participants()
    {
        return $this->hasMany(LiveSessionParticipant::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function liveStreamCredential()
    {
        return $this->belongsTo(LiveStreamCredential::class);
    }
}
