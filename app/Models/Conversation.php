<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

<<<<<<< HEAD
    protected $fillable = ['name', 'type', 'status', 'conversationable_id', 'conversationable_type'];
    
=======
    protected $fillable = ['name', 'owner_id', 'type', 'status', 'conversationable_id', 'conversationable_type'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_users');
    }

    public function conversationable()
    {
        return $this->morphTo();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
}
