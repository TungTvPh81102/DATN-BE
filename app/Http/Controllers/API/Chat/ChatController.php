<?php

namespace App\Http\Controllers\API\Chat;

use App\Events\GroupMessageSent;
use App\Events\UserStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\LiveSession;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Notifications\UserAddedToGroupChatNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToLocalTrait;

    const FOLDER_NAME = 'attachments';

    public function apiGetGroupChats(Request $request)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversations = Conversation::query()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('users:id,name,avatar')
                ->where('type', 'group')
                ->withCount('users')
                // ->where('conversationable_type', Course::class)
                ->where(function ($query) {
                    $query->whereNull('conversationable_type')
                        ->orWhere('conversationable_type', '!=', LiveSession::class);
                })
                ->get()
                ->map(function ($conversation) {
                    $data = $conversation->toArray();
                    $data['conversation_id'] = $data['id'];
                    $data['online_users'] = $conversation->users
                        ->filter(function ($user) use ($conversation) {
                            return $this->isUserOnlineInConversation($user->id, $conversation->id);
                        })
                        ->count();

                    $data['users'] = $conversation->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'avatar' => $user->avatar,
                            'is_blocked' => $user->pivot->is_blocked ?? false,
                        ];
                    });

                    unset($data['id']);
                    return $data;
                });

            if (empty($conversations)) {
                return $this->respondNotFound('Người dùng chưa có nhóm nào');
            }

            return $this->respondOk('Danh sách nhóm của người dùng: ' . $user->name, $conversations);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiGetStudentGroups()
    {
        try {
            $user = Auth::user();

            $conversations = Conversation::query()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('type', 'group')
                ->withCount('users')
                ->with(['users:id,name,avatar'])
                // ->where('conversationable_type', Course::class)
                ->where(function ($query) {
                    $query->whereNull('conversationable_type')
                        ->orWhere('conversationable_type', '!=', LiveSession::class);
                })
                ->get()
                ->map(function ($conversation) {
                    $data = $conversation->toArray();
                    $data['conversation_id'] = $data['id'];
                    $data['online_users'] = $conversation->users
                        ->filter(function ($user) use ($conversation) {
                            return $this->isUserOnlineInConversation($user->id, $conversation->id);
                        })
                        ->count();
                    unset($data['id']);
                    return $data;
                });

            if ($conversations->isEmpty()) {
                return $this->respondNotFound('Bạn chưa tham gia nhóm chat nào');
            }

            return $this->respondOk('Danh sách nhóm chat của bạn', $conversations);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError();
        }
    }

    public function apiInfoGroupChat(string $id)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversation = Conversation::query()
                ->where('owner_id', $user->id)
                ->where('type', 'group')
                ->whereNull('conversationable_id')
                ->whereNull('conversationable_type')
                ->with('users:id,name,avatar')
                ->find($id);

            if (empty($conversation)) {
                return $this->respondNotFound('Không tìm thấy nhóm');
            }

            return $this->respondOk('Thông tin nhóm: ' . $conversation->name, $conversation);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiGetRemainingMembers(string $conversationId)
    {
        try {
            $access = $this->checkAccess();

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $conversation = $this->getOwnedGroupChat($conversationId);

            $courses = Course::query()->where('user_id', $user->id)->pluck('id');

            $members = CourseUser::query()
                ->whereIn('course_id', $courses)
                ->pluck('user_id')
                ->toArray();

            $memberIds = $conversation->users()->pluck('user_id')->toArray();

            $remainingMembers = User::query()
                ->whereIn('id', $members)
                ->whereNotIn('id', $memberIds)
                ->get();

            return $this->respondOk('Danh sách thành viên chưa có trong nhóm: ' . $conversation->name, $remainingMembers);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiCreateGroupChat(Request $request)
    {
        try {
            DB::beginTransaction();

            $access = $this->checkAccess('instructor');

            if ($access !== true) {
                return $access;
            }

            $user = Auth::user();

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'members' => 'nullable|array',
                'members.*' => 'exists:users,id',
            ]);

            $courses = Course::query()->where('user_id', $user->id)->pluck('id');

            $members = CourseUser::query()
                ->whereIn('course_id', $courses)
                ->pluck('user_id')
                ->toArray();

            $conversation = Conversation::query()->create([
                'name' => $data['name'] ?? 'Nhóm của người dùng: ' . $user->name . Str::uuid(),
                'owner_id' => $user->id,
                'type' => 'group',
                'status' => '1',
            ]);

            $conversation->users()->attach($user->id);

            $validMembers = [];
            if (!empty($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $member_id) {
                    if ($member_id != $user->id && in_array($member_id, $members)) {
                        $validMembers[] = $member_id;
                    }
                }
            }

            if (!empty($validMembers)) {
                $conversation->users()->attach($validMembers);
            }

            DB::commit();

            return $this->respondCreated('Tạo nhóm trò chuyện thành công', $conversation);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiAddMemberGroupChat(Request $request, string $id)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);

            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $data = $request->validate([
                'members' => 'required|array',
                'members.*' => 'exists:users,id',
            ]);

            $existingMembers = $conversation->users()->pluck('user_id')->toArray();

            $validMembers = array_diff($data['members'], $existingMembers);

            if (empty($validMembers)) {
                return $this->respondBadRequest('Tất cả thành viên đã có trong nhóm');
            }

            $conversation->users()->attach($validMembers);

            $this->sendAddedToGroupNotifications($conversation, $validMembers);

            return $this->respondOk('Thêm thành viên vào nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiBlockMemberGroupChat(Request $request, string $id, string $memberId)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $memberId = User::query()->find($memberId);

            if (empty($memberId)) {
                return $this->respondNotFound('Không tìm thấy người dùng');
            }

            $conversation->users()->updateExistingPivot($memberId->id, ['is_blocked' => true]);

            return $this->respondOk('Chặn người dùng khỏi nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiUpdateInfoGroupChat(Request $request, string $id)
    {
        try {
            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $data = $request->validate([
                'name' => 'nullable|string|max:255',
            ]);

            $conversation->name = $data['name'] ?? $conversation->name;
            $conversation->save();

            return $this->respondOk('Cập nhật thông tin nhóm thành công', $conversation);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiKickMemberGroupChat(Request $request)
    {
        try {
            $data = $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'member_id' => 'required|exists:users,id',
            ]);

            $conversation = $this->getOwnedGroupChat($data['conversation_id']);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            if (!$conversation->users()->where('user_id', $data['member_id'])->exists()) {
                return $this->respondNotFound('Không tìm thấy thành viên trong nhóm');
            }

            if (!$conversation->users()->where('user_id', $data['member_id'])->exists()) {
                return $this->respondNotFound('Không tìm thấy thành viên trong nhóm');
            }

            if ($data['member_id'] == Auth::id()) {
                return $this->respondBadRequest('Bạn là chủ phòng nên không thể xoá bản thân');
            }

            $conversation->users()->detach($data['member_id']);

            return $this->respondOk('Xoá người dùng khỏi nhóm thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiDeleteGroupChat(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $access = $this->checkAccess('instructor');

            if ($access !== true) {
                return $access;
            }

            $conversation = $this->getOwnedGroupChat($id);
            if (!($conversation instanceof Conversation)) {
                return $conversation;
            }

            $conversation->users()->detach(Auth::id());

            $conversation->delete();

            DB::commit();

            return $this->respondOk('Xoá nhóm trò chuyện thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiGetMessage(Request $request, $conversationId)
    {
        try {
            $userId = Auth::id();
            $conversation = Conversation::query()->findOrFail($conversationId);

            if (!$conversation->users->contains($userId)) {
                return $this->respondError('Bạn không thuộc cuộc trò chuyện này');
            }

            $limit = $request->query('limit', 1000);
            $lastMessageId = $request->query('last_message_id');

            $cacheKey = "join_web_course_mely_conversation_{$conversationId}";

            $users = Cache::get($cacheKey, []);

            if (!in_array($userId, $users)) {
                $users[] = $userId;
                Cache::put($cacheKey, $users, now()->addMinutes(5));
            }

            $query = Message::query()
                ->where('conversation_id', $conversationId)
                ->with([
                    'parent.sender:id,name,avatar',
                    'sender:id,name,avatar'
                ])
                ->orderBy('created_at', 'asc')
                ->limit($limit);

            if ($lastMessageId) {
                $query->where('id', '<', $lastMessageId);
            }

            $messages = $query->get();
            broadcast(new UserStatusChanged('online', $conversationId))->toOthers();

            return $this->respondOk('Danh sách tin nhắn', [
                'messages' => $messages,
                'has_more' => $messages->count() === $limit
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function apiSendMessage(Request $request)
    {
        try {
            $data = $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'content' => 'nullable|string|max:255',
                'type' => 'required|in:text,image,file,video,audio',
                'parent_id' => 'nullable|exists:messages,id',
                'files' => 'nullable|array',
                'files.*' => 'file|max:10240',
            ]);

            DB::beginTransaction();

            $userId = Auth::id();
            $conversationId = $data['conversation_id'];
            $conversation = Conversation::query()->find($conversationId);

            if (!$conversation || !($conversation instanceof Conversation)) {
                return $this->respondNotFound('Cuộc trò chuyện không tồn tại');
            }

            if (!$conversation->users->contains($userId)) {
                return $this->respondError('Bạn không thuộc cuộc trò chuyện này');
            }

            $conversationUser = ConversationUser::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->first();

            if ($conversationUser && $conversationUser->is_blocked) {
                return $this->respondForbidden('Bạn đã bị chặn trong cuộc trò chuyện này');
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'parent_id' => $data['parent_id'] ?? null,
                'content' => $data['content'] ?? null,
                'type' => $data['type'],
                'meta_data' => $metaData ?? null,
            ]);

            $mediaData = [];
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $filePaths = $this->uploadMultiple($files, self::FOLDER_NAME);

                if (!$filePaths) {
                    return $this->respondBadRequest('Có lỗi xảy ra khi tải lên tệp tin');
                }

                foreach ($files as $index => $file) {
                    if (!isset($filePaths[$index])) {
                        continue;
                    }

                    $file_path = $filePaths[$index];
                    $file_type = $file->getClientMimeType();
                    $file_size = $file->getSize();
                    $file_name = $file->getClientOriginalName();

                    $mediaData[] = [
                        'file_name' => $file_name,
                        'file_path' => $file_path,
                        'file_type' => $file_type,
                        'file_size' => $file_size,
                    ];
                }
            }

            $message->update(['meta_data' => $mediaData]);

            $recipient = $conversation->users->filter(function ($user) {
                return $user->id !== Auth::id();
            })->first();

            if (!$recipient) {
                return $this->respondError('Không có người nhận trong cuộc trò chuyện này');
            }

            broadcast(new \App\Events\MessageSentEvent($message, $conversation))->toOthers();

            $cacheKey = "join_web_course_mely_conversation_{$data['conversation_id']}";
            $users = Cache::get($cacheKey, [auth()->id()]);

            if (!in_array($recipient->id, $users)) {
                $users[] = $recipient->id;
                Cache::put($cacheKey, $users);

                $this->notifyConversationMembers($conversation, $message);
            }

            DB::commit();

            return $this->respondOk('Gửi tin nhắn thành công', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiGetOnlineUsers(Request $request)
    {
        try {
            $users = User::all();

            $onlineUsers = [];

            foreach ($users as $user) {
                $onlineUsers[$user->id] = Cache::has('user_status_' . $user->id);
            }

            return $this->respondOk('Danh sách người dùng đang hoạt đông', $onlineUsers);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiGetDirectChats(Request $request)
    {
        try {
            $userId = Auth::id();
            $page = $request->query('page', 1);
            $perPage = 4;

            $conversations = Conversation::query()
                ->whereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->where('type', 'direct')
                ->with(['users:id,name,avatar'])
                ->withCount('messages')
                ->orderByDesc('updated_at')
                ->take($perPage)
                ->get();

            if ($conversations->isEmpty()) {
                return $this->respondNotFound('Bạn không có cuộc trò chuyện cá nhân nào');
            }

            $users = $conversations->flatMap(function ($conversation) use ($userId) {
                return $conversation->users->map(function ($user) use ($conversation, $userId) {
                    if ($user->id !== $userId) {
                        $data = $user->toArray();
                        $data['conversation_id'] = $conversation->id;
                        $data['is_online'] = $this->isUserOnlineInConversation($user->id, $conversation->id);
                        $data['last_message_at'] = $conversation->updated_at;
                        $data['messages_count'] = $conversation->messages_count;
                        unset($data['pivot']);
                        return $data;
                    }
                })->filter();
            });

            if ($users->isEmpty()) {
                return $this->respondNotFound('Không tìm thấy người dùng nào trong các cuộc trò chuyện cá nhân');
            }

            $uniqueUsers = $users->take($perPage)->values();

            return $this->respondOk('Danh sách cuộc trò chuyện cá nhân', $uniqueUsers);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError();
        }
    }

    public function apiGetMyInstructors()
    {
        try {
            $studentId = Auth::id();

            $purchasedCourseIds = CourseUser::query()
                ->where('user_id', $studentId)
                ->pluck('course_id')
                ->toArray();

            $instructors = User::query()
                ->whereIn('id', function ($query) use ($purchasedCourseIds) {
                    $query->select('user_id')
                        ->from('courses')
                        ->whereIn('id', $purchasedCourseIds);
                })
                ->where('role', 'instructor')
                ->select([
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'phone',
                ])
                ->with(['courses' => function ($query) use ($purchasedCourseIds) {
                    $query->whereIn('id', $purchasedCourseIds)
                        ->select(['id', 'user_id', 'name', 'thumbnail']);
                }])
                ->get();

            if ($instructors->isEmpty()) {
                return $this->respondNotFound('Bạn chưa có giảng viên nào');
            }

            $formattedInstructors = $instructors->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'email' => $instructor->email,
                    'avatar' => $instructor->avatar,
                    'phone' => $instructor->phone,
                    'courses' => $instructor->courses->map(function ($course) {
                        return [
                            'id' => $course->id,
                            'name' => $course->name,
                            'thumbnail' => $course->thumbnail
                        ];
                    })
                ];
            });

            return $this->respondOk('Danh sách giảng viên của bạn', $formattedInstructors);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError();
        }
    }

    public function apiStartDirectChat(Request $request)
    {
        try {
            $data = $request->validate([
                'recipient_id' => 'required|exists:users,id',
            ]);

            $recipientId = $data['recipient_id'];
            $userId = Auth::id();

            if ($userId == $recipientId) {
                return $this->respondError('Bạn không thể bắt đầu cuộc trò chuyện với chính mình');
            }

            $conversation = Conversation::query()
                ->where('type', 'direct')
                ->whereHas('users', function ($query) use ($userId, $recipientId) {
                    $query->where('user_id', $userId);
                })
                ->whereHas('users', function ($query) use ($recipientId) {
                    $query->where('user_id', $recipientId);
                })
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'type' => 'direct',
                    'owner_id' => $userId,
                    'name' => 'Cuộc trò chuyện 2 người với nhau ' . Str::uuid()
                ]);

                $conversation->users()->attach([$userId, $recipientId]);
            }

            return $this->respondOk('Tạo trò chuyện thành công', $conversation->load('users'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondServerError();
        }
    }

    public function apiToggleBlockUserInChat(Request $request, string $conversationId, string $action, string $userId)
    {
        try {
            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return $this->respondNotFound('Không tìm thấy cuộc trò chuyện');
            }

            if ($conversation->owner_id != Auth::id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện chức năng này');
            }

            if ($userId == Auth::id()) {
                return $this->respondBadRequest('Bạn không thể chặn chính mình');
            }

            $conversationUser = ConversationUser::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->first();

            if (!$conversationUser) {
                return $this->respondNotFound('Người dùng không thuộc cuộc trò chuyện này');
            }

            $isBlocked = $action === 'block';
            $conversationUser->update([
                'is_blocked' => $isBlocked
            ]);

            $message = $isBlocked
                ? 'Đã chặn người dùng trong cuộc trò chuyện này thành công'
                : 'Đã bỏ chặn người dùng trong cuộc trò chuyện này thành công';

            return $this->respondOk($message);
        } catch (\Exception $e) {
            $this->logError($e, [
                'conversationId' => $conversationId,
                'userId' => $userId,
                'action' => $action
            ]);

            return $this->respondServerError();
        }
    }

    private function notifyConversationMembers($conversation,  $message)
    {
        Log::info($conversation);
        $senderId = Auth::id();

        $recipientUsers = $conversation->users;
        broadcast(new UserStatusChanged('offline', $conversation->id))->toOthers();
        foreach ($recipientUsers as $user) {
            if ($user->id == $senderId) continue;

            if ($this->isUserOnlineInConversation($user->id, $conversation)) {
                continue;
            }

            $user->notify(new MessageNotification($message));
        }
    }

    private function isUserOnlineInConversation(string $userId, string $conversationId)
    {
        $key = "join_web_course_mely_{$userId}";
        return Cache::has($key);
    }

    private function setUserOnline($userId, $conversationId)
    {
        $key = "join_web_course_mely_{$userId}";
        Cache::put($key, true, now()->addMinutes(5));
    }

    private function getOwnedGroupChat(string $id)
    {
        $access = $this->checkAccess('instructor');
        if ($access !== true) {
            return $access;
        }

        $user = Auth::user();

        $conversation = Conversation::query()
            ->where('owner_id', $user->id)
            ->where('type', 'group')
            ->find($id);

        if (empty($conversation)) {
            throw new \Exception('Không tìm thấy nhóm');
        }

        return $conversation;
    }

    private function sendAddedToGroupNotifications($conversation, array $memberIds)
    {
        $newMembers = User::query()->whereIn('id', $memberIds)->get();

        foreach ($newMembers as $member) {
            $member->notify(new UserAddedToGroupChatNotification($conversation));
        }
    }

    protected function checkAccess(?string $role = null)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->respondUnauthorized('Bạn không có quyền truy cập');
        }

        if ($role && !$user->hasRole($role)) {
            return $this->respondForbidden('Bạn không có quyền thực hiện chức năng');
        }

        return true;
    }
}
