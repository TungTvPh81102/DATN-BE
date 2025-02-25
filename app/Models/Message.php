<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'parent_id',
        'content',
        'type',
        'meta_data',
    ];
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

<<<<<<< HEAD
=======
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
    public function media()
    {
        return $this->hasMany(Media::class, 'message_id');
    }
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
}
