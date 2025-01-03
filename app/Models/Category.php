<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'icon',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->withTrashed();
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'categorizable');
    }
}
