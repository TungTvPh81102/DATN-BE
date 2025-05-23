<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'display_name'];
}
