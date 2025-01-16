<?php

namespace App\Http\Controllers\API\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Search\SearchRequest;
use App\Traits\LoggableTrait;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class SearchController extends Controller
{
    use LoggableTrait, ApiResponseHelpers;
    public function search(SearchRequest $request)
    {
        try {
            $data = $request->validated();

            $query = $request->input('query');

            $courses = DB::table('courses')
                ->select('id', 'category_id', 'name', 'slug', 'price', 'price_sale', 'thumbnail', 'total_student', 'duration', 'description')
                ->where('status', 'approved')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->get();

            $posts = DB::table('posts')
                ->select('id', 'category_id ', 'title', 'slug', 'thumbnail', 'is_hot', 'published_at', 'content')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                        ->orWhere('content', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->get();
            return response()->json([
                'status' => true,
                'message' => isEmpty($posts) && isEmpty($courses) ? 'Không tìm thấy khóa học, bài viết' : '',
                'courses' => $courses,
                'posts' => $posts,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại',
                'data' => $request->all(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
