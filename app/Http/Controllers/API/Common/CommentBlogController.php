<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Posts\ReplyCommentBlogRequest;
use App\Http\Requests\API\Posts\StoreCommentBlogRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentBlogController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getCommentBlog(Request $request, $postId)
    {
        try {
            $commentBlogs = Comment::query()
                ->with([
                    'user:id,name,avatar,email',
                    'replies.user:id,name,email,avatar'
                ])
                ->withCount('replies')
                ->where('commentable_type', Post::class)
                ->where('commentable_id', $postId)
                ->where('parent_id', null)
                ->get();

            if (!$commentBlogs) {
                return $this->respondNotFound('Không tìm thấy bình luận');
            }

            $filteredComments = $commentBlogs->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                        'avatar' => $comment->user->avatar,
                    ],
                    'replies_count' => $comment->replies_count,
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'created_at' => $reply->created_at,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                                'email' => $reply->user->email,
                                'avatar' => $reply->user->avatar,
                            ]
                        ];
                    })
                ];
            });

            return $this->respondSuccess('Danh sách bình luận', $filteredComments);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function storeCommentBlog(StoreCommentBlogRequest $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $data = $request->validated();

            $posts = Post::query()->find($data['post_id']);

            if (!$posts) {
                return $this->respondNotFound('Không tìm thấy bài viết');
            }

            $comment = Comment::query()->create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'commentable_id' => $posts->id,
                'commentable_type' => Post::class,
                'parent_id' => null,
            ]);

            return $this->respondCreated('Bình luận thành công', $comment);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function getReplies(Request $request, string $commentId)
    {
        try {
            $parentComment = Comment::query()
                ->find($commentId);

            if (!$parentComment) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            $offset = $request->get('offset', 0);
            $limit = $request->get('limit', 3);

            $replies = $parentComment->replies()
                ->latest()
                ->offset($offset)
                ->limit($limit)
                ->get();
            if (!$replies) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            return $this->respondOk('Danh sách phản hồi', $replies);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function reply(ReplyCommentBlogRequest $request, $commentId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $data = $request->validated();

            $parentComment = Comment::query()->find($commentId);

            if (!$parentComment) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            $reply = Comment::query()->create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'parent_id' => $parentComment->id,
                'commentable_id' => $parentComment->commentable_id,
                'commentable_type' => Post::class,
            ]);

            return $this->respondCreated('Phản hồi bình luận thành công', $reply);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function deleteComment(string $commentId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            $comment = Comment::query()->find($commentId);

            if (!$comment) {
                return $this->respondNotFound('Không tìm thấy bình luận');
            }

            $isRootComment = $comment->parent_id === null;

            if ($isRootComment && $comment->user_id !== $user->id) {
                return $this->respondForbidden('Bạn không có quyền xóa bình luận gốc này');
            }

            if (!$isRootComment && $comment->user_id !== $user->id) {
                return $this->respondForbidden('Bạn không có quyền xóa bình luận này');
            }

            if ($isRootComment) {
                $comment->replies()->delete();
            }

            $comment->delete();

            return $this->respondOk('Xóa bình luận thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

}