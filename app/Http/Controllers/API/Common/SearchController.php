<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Search\SearchRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isEmpty;

class SearchController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function search(SearchRequest $request)
    {
        try {
            $query = $request->input('q');

            $results = [];

            $courses = DB::table('courses')
                ->select('id', 'name', 'slug', 'thumbnail')
                ->where('status', 'approved')
                ->whereRaw("MATCH(name, description) AGAINST(? IN BOOLEAN MODE)", [$query])
                ->limit(5)
                ->get();

            if ($courses->isNotEmpty()) {
                $results['courses'] = $courses;
            }

            $posts = DB::table('posts')
                ->select('id', 'title', 'slug', 'thumbnail')
                ->where('status', 'published')
                ->whereRaw("MATCH(title, content, description) AGAINST(? IN BOOLEAN MODE)", [$query])
                ->limit(5)
                ->get();

            if ($posts->isNotEmpty()) {
                $results['posts'] = $posts;
            }

            $instructors = DB::table('users')
                ->select('id', 'name', 'email', 'avatar')
                ->whereRaw("MATCH(name, email) AGAINST(? IN BOOLEAN MODE)", [$query])
                ->limit(5)
                ->get();

            if ($instructors->isNotEmpty()) {
                $results['instructors'] = $instructors;
            }

            if (empty($results)) {
                return $this->respondNotFound('Không có dữ liệu');
            }

            return $this->respondOk('Kết quả tìm kiếm', $results);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Internal Server Error');
        }
    }
}
