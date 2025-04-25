<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'owner_id', 'type', 'status', 'conversationable_id', 'conversationable_type'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users')->withPivot('is_blocked', 'last_read_at')->withTimestamps();
    }

    public function conversationable()
    {
        return $this->morphTo();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
