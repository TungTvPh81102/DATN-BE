<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;
}
