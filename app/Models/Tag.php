<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->morphToMany(Post::class, 'taggable');
    }

    public function taggable () {
        return $this->morphTo();
    }
}
