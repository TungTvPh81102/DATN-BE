<?php

namespace App\Http\Controllers\API\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\ReplyCommentLessonRequest;
use App\Http\Requests\API\Lessons\StoreCommentLessonRequest;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Reaction;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Blaspsoft\Blasp\Facades\Blasp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CommentLessonController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getCommentLesson(Request $request, $lessonId)
    {
        try {
            $userId = Auth::id();

            $commentLessons = Comment::query()
                ->with([
                    'user:id,name,avatar,email',
                    'replies.user:id,name,email,avatar',
                    'reactions.user:id,name,email,avatar',
                    'replies.reactions.user:id,name,email,avatar'
                ])
                ->withCount('replies')
                ->where('commentable_type', Lesson::class)
                ->where('commentable_id', $lessonId)
                ->where('parent_id', null)
                ->get();

            if (!$commentLessons) {
                return $this->respondNotFound('Không tìm thấy bình luận');
            }

            $filteredComments = $commentLessons->map(function ($comment) use ($userId) {
                $reactions = $comment->reactions->where('reactable_id', $comment->id);
                $likeCount = $reactions->where('type', 'like')->count();
                $loveCount = $reactions->where('type', 'love')->count();
                $hahaCount = $reactions->where('type', 'haha')->count();
                $wowCount = $reactions->where('type', 'wow')->count();
                $sadCount = $reactions->where('type', 'sad')->count();
                $angryCount = $reactions->where('type', 'angry')->count();
                $totalReactions = $likeCount + $loveCount + $hahaCount + $wowCount + $sadCount + $angryCount;

                $userReaction = $reactions->first(function ($reaction) use ($userId) {
                    return $reaction->user_id == $userId;
                });

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
                    'reactions' => $comment->reactions->map(function ($reaction) {
                        return [
                            'id' => $reaction->id,
                            'type' => $reaction->type,
                            'user' => [
                                'id' => $reaction->user->id,
                                'name' => $reaction->user->name,
                                'email' => $reaction->user->email,
                                'avatar' => $reaction->user->avatar,
                            ]
                        ];
                    }),
                    'reaction_counts' => [
                        'like' => $likeCount,
                        'love' => $loveCount,
                        'haha' => $hahaCount,
                        'wow' => $wowCount,
                        'sad' => $sadCount,
                        'angry' => $angryCount,
                        'total' => $totalReactions
                    ],
                    'user_reaction' => $userReaction ? $userReaction->type : null,
                    'replies' => $comment->replies->map(function ($reply) use ($userId) {
                        $replyReactions = $reply->reactions;
                        $replyLikeCount = $replyReactions->where('type', 'like')->count();
                        $replyLoveCount = $replyReactions->where('type', 'love')->count();
                        $replyHahaCount = $replyReactions->where('type', 'haha')->count();
                        $replyWowCount = $replyReactions->where('type', 'wow')->count();
                        $replySadCount = $replyReactions->where('type', 'sad')->count();
                        $replyAngryCount = $replyReactions->where('type', 'angry')->count();
                        $replyTotalReactions = $replyLikeCount + $replyLoveCount + $replyHahaCount +
                            $replyWowCount + $replySadCount + $replyAngryCount;

                        $userReplyReaction = $replyReactions->first(function ($reaction) use ($userId) {
                            return $reaction->user_id == $userId;
                        });

                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'created_at' => $reply->created_at,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                                'email' => $reply->user->email,
                                'avatar' => $reply->user->avatar,
                            ],
                            'reactions' => $reply->reactions->map(function ($reaction) {
                                return [
                                    'id' => $reaction->id,
                                    'type' => $reaction->type,
                                    'user' => [
                                        'id' => $reaction->user->id,
                                        'name' => $reaction->user->name,
                                        'email' => $reaction->user->email,
                                        'avatar' => $reaction->user->avatar,
                                    ]
                                ];
                            }),
                            'reaction_counts' => [
                                'like' => $replyLikeCount,
                                'love' => $replyLoveCount,
                                'haha' => $replyHahaCount,
                                'wow' => $replyWowCount,
                                'sad' => $replySadCount,
                                'angry' => $replyAngryCount,
                                'total' => $replyTotalReactions
                            ],
                            'user_reaction' => $userReplyReaction ? $userReplyReaction->type : null,
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

    public function storeCommentLesson(StoreCommentLessonRequest $request)
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

            $lessons = Lesson::query()->find($data['lesson_id']);

            if (!$lessons) {
                return $this->respondNotFound('Không tìm thấy lớp học');
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
                'commentable_id' => $lessons->id,
                'commentable_type' => Lesson::class,
                'parent_id' => null,
            ]);

            return $this->respondCreated('Bình luận thành công', $comment);
        } catch (\Exception $e) {
            Log::error('Error in storeCommentLessonBlog: ' . $e->getMessage());
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

    public function reply(ReplyCommentLessonRequest $request, $commentId)
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
                return $this->respondError('Bình luận chứa từ ngữ không phù hợp.');
            }

            $reply = Comment::query()->create([
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'parent_id' => $parentComment->id,
                'commentable_id' => $parentComment->commentable_id,
                'commentable_type' => Lesson::class,
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
                $replyIds = $comment->replies()->pluck('id')->toArray();

                if (!empty($replyIds)) {
                    Reaction::query()->where('reactable_type', Comment::class)
                        ->whereIn('reactable_id', $replyIds)
                        ->delete();
                }

                $comment->replies()->delete();
            }

            Reaction::query()->where('reactable_id', $comment->id)
                ->where('reactable_type', Comment::class)
                ->delete();

            $comment->delete();

            return $this->respondOk('Xóa bình luận thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }
}
