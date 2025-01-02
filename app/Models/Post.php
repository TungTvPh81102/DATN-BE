<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PRIVATE = 'private';

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function categories() {
        return $this->morphToMany(Category::class, 'categorizable');
    }
}
