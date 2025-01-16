<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_name',
        'degree',
        'major',
        'start_date',
        'end_date',
        'certificates',
        'qa_systems',
    ];
}
