<?php

namespace App\Http\Controllers\API\Posts;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $title = 'Quản lý bài viết';
            $subTitle = 'Danh sách bài viết';

            $categories = Category::query()->get();

            $searchPost = $request->input('searchPost');

            // kiểm tra xem có từ khóa không
            if (!empty($searchPost)) {
                $posts =  Post::with('user')
                    ->where('title', 'LIKE', '%' . $searchPost . '%')
                    ->paginate(10);
            } else {
                $posts = Post::with('user')->paginate(10);
            }

            // Kiểm tra xem trong collection có phần tử nào không
            $message =$posts->isEmpty() ? 'Không có bản ghi nào' :  '';
            $key =$posts->isEmpty() ? 'success' :  '';
            session()->flash($key,$message);

            return view('posts.index', compact([
                'title',
                'subTitle',
                'categories',
                'posts'
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
