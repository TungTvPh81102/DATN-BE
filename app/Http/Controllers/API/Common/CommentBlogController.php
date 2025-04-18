<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Posts\ReplyCommentBlogRequest;
use App\Http\Requests\API\Posts\StoreCommentBlogRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
            // Kiểm tra xem người dùng có bị chặn không
            $blockKey = "comment_block:user_{$user->id}";
            if (Redis::exists($blockKey)) {
                $ttl = Redis::ttl($blockKey);
                $blockUntil = Carbon::now()->addSeconds($ttl);
                $minutes = floor($ttl / 60);
                $seconds = $ttl % 60;
                $formattedCountdown = sprintf('%02d:%02d', $minutes, $seconds);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn đã bị cấm bình luận đến ' . $blockUntil->toDateTimeString() . '.',
                    'countdown' => $ttl,
                    'formatted_countdown' => $formattedCountdown
                ], 400);
            }
            $data = $request->validated();

            $posts = Post::query()->find($data['post_id']);

            if (!$posts) {
                return $this->respondNotFound('Không tìm thấy bài viết');
            }
            $customCheck = function ($text, $profanities) {
                $text = strtolower($text);
                foreach ($profanities as $word) {
                    if (stripos($text, strtolower($word)) !== false) {
                        return true;
                    }
                }
                return false;
            };

            $profanities = config('blasp.profanities', []);

            if ($customCheck($data['content'], $profanities)) {
                $violationKey = "comment_violations:user_{$user->id}";
                $violations = Redis::incr($violationKey);
                if ($violations === 1) {
                    Redis::expire($violationKey, 3600);
                }
                if ($violations > config('comments.max_violations')) {
                    Redis::setex($blockKey, config('comments.block_duration'), true);
                    Redis::del($violationKey);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bạn đã bị cấm bình luận trong 1 tiếng do sử dụng từ ngữ không phù hợp quá nhiều lần.',
                        'countdown' => 3600
                    ], 400);
                }

                return $this->respondError('Bình luận chứa từ ngữ không phù hợp.');
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
            Log::error('Error in storeCommentBlog: ' . $e->getMessage());
            if ($e instanceof RedisException) {
                Log::error('Redis error: ' . $e->getMessage());
                return $this->respondServerError('Hệ thống gặp lỗi, vui lòng thử lại sau.');
            }
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }
    public function getCommentBlockTime(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            // Kiểm tra xem người dùng có bị chặn không
            $blockKey = "comment_block:user_{$user->id}";
            if (!Redis::exists($blockKey)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bạn không bị cấm bình luận.',
                    'is_blocked' => false,
                ], 200);
            }

            // Lấy thời gian còn lại từ Redis
            $ttl = Redis::ttl($blockKey);
            $blockUntil = Carbon::now()->addSeconds($ttl);
            $minutes = floor($ttl / 60);
            $seconds = $ttl % 60;
            $formattedCountdown = sprintf('%02d:%02d', $minutes, $seconds);

            return response()->json([
                'status' => 'success',
                'message' => 'Bạn đang bị cấm bình luận.',
                'is_blocked' => true,
                'countdown' => $ttl,
                'formatted_countdown' => $formattedCountdown,
                'block_until' => $blockUntil->toDateTimeString(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getCommentBlockTime: ' . $e->getMessage());
            if ($e instanceof RedisException) {
                Log::error('Redis error: ' . $e->getMessage());
                return $this->respondServerError('Hệ thống gặp lỗi, vui lòng thử lại sau.');
            }
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

            // Kiểm tra quyền truy cập của người dùng
            if (!$user) {
                return $this->respondUnauthorized('Bạn không có quyền truy cập');
            }

            // Lấy dữ liệu đã validated từ request
            $data = $request->validated();

            // Tìm bình luận cha (comment parent)
            $parentComment = Comment::find($commentId);

            // Kiểm tra nếu không tìm thấy bình luận cha
            if (!$parentComment) {
                return $this->respondNotFound('Không có bình luận cha');
            }

            // Kiểm tra xem content có hợp lệ không
            if (empty($data['content'])) {
                return $this->respondBadRequest('Nội dung không được để trống');
            }

            // Tạo bình luận phản hồi
            $reply = Comment::create([
                'user_id' => $user->id,
                'content' => $data['content'], // Nội dung phản hồi
                'parent_id' => $parentComment->id, // Lưu parent_id là id của bình luận cha
                'commentable_id' => $parentComment->commentable_id, // Lưu commentable_id như của bình luận cha
                'commentable_type' => $parentComment->commentable_type, // Lưu commentable_type như của bình luận cha
            ]);

            return $this->respondCreated('Phản hồi bình luận thành công', $reply);
        } catch (\Exception $e) {
            // Log lỗi và trả về thông báo lỗi server
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
            $isAdmin = $user->role === 'admin'; // Giả sử có cột role trong bảng users

            if (!$isAdmin) {
                // Nếu là bình luận gốc, chỉ cho phép xóa nếu không có phản hồi
                if ($isRootComment && $comment->replies()->count() > 0) {
                    return $this->respondForbidden('Bình luận này có phản hồi và không thể xóa, chỉ có thể ẩn');
                }

                // Chỉ cho phép xóa nếu là bình luận của chính họ
                if ($comment->user_id !== $user->id) {
                    return $this->respondForbidden('Bạn không có quyền xóa bình luận này');
                }
            }

            $comment->delete();

            return $this->respondOk('Xóa bình luận thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
    public function updateComment(Request $request, string $commentId)
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

            // Chỉ admin hoặc chủ bình luận mới có quyền sửa
            if ($comment->user_id !== $user->id && $user->role !== 'admin') {
                return $this->respondForbidden('Bạn không có quyền chỉnh sửa bình luận này');
            }

            // Validate nội dung mới
            $validatedData = $request->validate([
                'content' => 'required|string|max:500', // Giới hạn số ký tự nếu cần
            ]);

            $comment->update([
                'content' => $validatedData['content'],
                'updated_at' => now(),
            ]);

            return $this->respondOk('Cập nhật bình luận thành công');
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError();
        }
    }
}
