<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'status', 'conversationable_id', 'conversationable_type'];
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users');
    }
}
