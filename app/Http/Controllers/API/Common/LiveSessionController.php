<?php

namespace App\Http\Controllers\API\Common;

use App\Events\LiveChatMessageSent;
use App\Events\UserJoinedLiveSession;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Models\Message;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LiveSessionController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function getLiveSessions(Request $request)
    {
        try {
            $status = $request->query('status', 'live');

            $query = LiveSession::query();

            if ($status === 'upcoming') {
                $query->where('starts_at', '>', now())
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('actual_end_time')
                            ->orWhere('actual_end_time', '>', now());
                    });
            } elseif ($status === 'live') {
                $query->where('starts_at', '<=', now())
                    ->where('actual_start_time', null);
            } elseif ($status === 'all') {
                $query->where(function ($subQuery) {
                    $subQuery->where(function ($liveQuery) {
                        $liveQuery->where('starts_at', '<=', now())
                            ->whereNull('actual_start_time');
                    })->orWhere(function ($upcomingQuery) {
                        $upcomingQuery->where('starts_at', '>', now())
                            ->where(function ($subQuery) {
                                $subQuery->whereNull('actual_end_time')
                                    ->orWhere('actual_end_time', '>', now());
                            });
                    });
                });
            }

            $liveStreams = $query->with(['instructor'])
                ->orderBy('starts_at', 'desc')
                ->paginate(10);

            $customData = [
                'current_page' => $liveStreams->currentPage(),
                'per_page' => $liveStreams->perPage(),
                'total' => $liveStreams->total(),
                'data' => $liveStreams->getCollection()->transform(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'title' => $item->title,
                        'thumbnail' => $item->thumbnail ? Storage::url($item->thumbnail) : null,
                        'description' => $item->description,
                        'visibility' => $item->visibility,
                        'status' => $item->status,
                        'starts_at' => $item->starts_at,
                        'instructor' => [
                            'code' => $item->instructor->code,
                            'name' => $item->instructor->name,
                            'avatar' => $item->instructor->avatar,
                        ],
                    ];
                }),
            ];

            $countUpcoming = LiveSession::where('starts_at', '>', now())
                ->where(function ($subQuery) {
                    $subQuery->whereNull('actual_end_time')
                        ->orWhere('actual_end_time', '>', now());
                })->count();
            $countLive = LiveSession::where('starts_at', '<=', now())
                ->where('actual_start_time', null)
                ->count();

            return $this->respondOk('Thao tác thành công', [
                'live_streams' =>  $customData,
                'counts' => [
                    'upcoming' => $countUpcoming,
                    'live' => $countLive,
                ],
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function show(string $code)
    {
        try {
            $user = Auth::check() ? Auth::user() : null;

            $liveSession = LiveSession::query()
                ->with([
                    'liveStreamCredential:id,mux_playback_id',
                    'instructor' => function ($query) {
                        $query->select('id', 'code', 'name', 'avatar', 'email');
                        $query->with(['profile' => function ($profileQuery) {
                            $profileQuery->select('user_id', 'phone', 'address', 'about_me');
                        }]);
                    },
                    'conversation.messages' => function ($query) {
                        $query->select('id', 'conversation_id', 'sender_id', 'content', 'created_at')
                            ->orderBy('created_at', 'asc')->limit(20);
                        $query->with(['sender' => function ($query) {
                            $query->select('id', 'name', 'avatar');
                        }]);
                    },
                    'participants' => function ($query) {
                        $query->select('user_id', 'live_session_id', 'role');
                    },
                    'conversation.users' => function ($query) {
                        $query->select('conversation_id', 'user_id', 'is_blocked');
                    },
                ])
                ->where(function ($query) {
                    $query->where('status', 'live')
                        ->orWhere(function ($query) {
                            $query->where('status', 'upcoming')
                                ->where('starts_at', '>', now());
                        });
                })
                ->where('code', $code)
                ->first();

            if (!$liveSession) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }


            if (!$user) {
                broadcast(new UserJoinedLiveSession($liveSession->id, null));
            }

            broadcast(new UserJoinedLiveSession($liveSession->id, $user));

            return $this->respondOk('Thông tin phiên live', $liveSession);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function joinLiveSession($code)
    {
        try {
            $liveSession = LiveSession::query()
                ->with('instructor')
                ->where('code', $code)
                ->where(function ($query) {
                    $query->where('status', 'live')
                        ->orWhere(function ($query) {
                            $query->where('status', 'upcoming')
                                ->where('starts_at', '>', now());
                        });
                })
                ->first();

            if (empty($liveSession)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            $user = Auth::check() ? Auth::user() : null;

            if (!$user) {
                broadcast(new UserJoinedLiveSession($liveSession->id, null));

                return $this->respondOk('Xem phiên live thành công', [
                    'live_session' => $liveSession,
                    'user' => null
                ]);
            }

            $conversation = Conversation::query()
                ->where('conversationable_type', operator: LiveSession::class)
                ->where('conversationable_id', $liveSession->id)
                ->first();

            if (empty($conversation)) {
                return $this->respondNotFound('Không tìm thấy phiên live');
            }

            $existingParticipant = LiveSessionParticipant::query()->where([
                'user_id' => $user->id,
                'live_session_id' =>  $liveSession->id
            ])->first();

            if (!$existingParticipant) {
                LiveSessionParticipant::create([
                    'user_id' => $user->id,
                    'live_session_id' => $liveSession->id,
                    'role' => 'viewer',
                    'joined_at' => now()
                ]);
            }

            $existingConversationUser = ConversationUser::query()->where([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id
            ])->first();

            if (!$existingConversationUser) {
                ConversationUser::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'is_blocked' => false,
                    'last_read_at' => now()
                ]);
            }

            broadcast(new UserJoinedLiveSession($liveSession->id, $user));

            return $this->respondOk('Tham gia phiên live thành công', [
                'live_session' => $liveSession,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                ] : null
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function sendMessage(Request $request, $liveSessionId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->respondForbidden('Bạn cần đăng nhập để thực hiện chức năng này');
            }

            $validated = $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $liveSession = LiveSession::query()->find($liveSessionId);

            $participant = LiveSessionParticipant::where([
                'user_id' => $user->id,
                'live_session_id' => $liveSession->id
            ])->first();

            if (!$participant) {
                LiveSessionParticipant::create([
                    'user_id' => $user->id,
                    'live_session_id' => $liveSession->id,
                    'role' => 'viewer',
                    'joined_at' => now()
                ]);
            }

            $conversation = Conversation::firstOrCreate(
                [
                    'conversationable_type' => LiveSession::class,
                    'conversationable_id' => $liveSession->id
                ],
                [
                    'name' => $validated['title'] ?? 'Phiên live',
                    'type' => 'group',
                    'status' => 1,
                    'owner_id' => $liveSession->instructor_id,
                    'conversationable_type' => LiveSession::class,
                    'conversationable_id' => $liveSession->id,
                ]
            );

            $existingConversationUser = ConversationUser::query()->where([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id
            ])->first();

            if (!$existingConversationUser) {
                ConversationUser::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'is_blocked' => false,
                    'last_read_at' => now()
                ]);
            }

            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $validated['message'],
                'type' => 'text',
                'meta_data' => null
            ]);

            broadcast(new LiveChatMessageSent($message, $user, $liveSessionId));

            return $this->respondOk('Gửi tin nhắn thành công', $message);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }
}
