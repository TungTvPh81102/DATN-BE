<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'slug',
        'content',
        'is_free_preview',
        'order',
        'type',
        'is_supplement',
        'lessonable_type',
        'lessonable_id',
    ];

    public $attributes = [
        'is_free_preview' => 0,
        'order' => 0,
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function lessonable()
    {
        return $this->morphTo();
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
