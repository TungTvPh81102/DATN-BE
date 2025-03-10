<?php

namespace App\Http\Controllers\Admin;

use App\Events\GroupMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chats\StoreGroupChatRequest;
use App\Http\Requests\Admin\Chats\StoreSendMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Group;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerTrait;

class ChatController extends Controller
{
    use LoggableTrait, UploadToLocalTrait;

    const FOLDER = "messages";

    public function index()
    {
        $data = $this->getAdminsAndChannels();
        return view(
            'chats.chat-realtime',
            [
                'data' => $data
            ]
        );
    }
    public function createGroupChat(StoreGroupChatRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Tạo nhóm chat
            $conversation = Conversation::create([
                'name' => $validated['name'] ?? 'Nhóm ẩn danh',
                'owner_id' => auth()->id(),
                'type' => $validated['type'],
                'status' => '1',
                'conversationable_id' => null,
                'conversationable_type' => null,
            ]);
            $conversation->users()->attach(auth()->id());

            if ($request->has('members') && is_array($request->members)) {
                foreach ($request->members as $member_id) {
                    if ($member_id == auth()->id()) {
                        continue; // Bỏ qua owner
                    }

                    $user = User::find($member_id);
                    if ($user) {
                        $conversation->users()->attach($member_id);
                    }
                }
            }
            DB::commit();
            $data = $this->getAdminsAndChannels();
            $data['conversation'] = $conversation;

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm nhóm thành công',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, $request->all());

            return response()->json([
                'status' => 'error',
                'message' => 'Thao tác không thành công',
            ]);
        }
    }

    public function sendGroupMessage(StoreSendMessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'sender_id' => auth()->id(),
                'parent_id' => $validated['parent_id'] ?? null,

                'content' => $validated['content'] ?? null,
                'type' => $validated['type'],
                'meta_data' => $validated['meta_data'] ?? null,
            ]);

            if ($request->hasFile('input_file')) {
                $file = $request->file('input_file');
                $filePath = $this->uploadToLocal($file, self::FOLDER);

                $media = Media::create([
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'message_id' => $message->id,
                ]);

                $message->update(['meta_data' => json_encode(['media_id' => $media->id])]);
            }
            $message->load(['sender', 'media']);

            DB::commit();
            broadcast(new GroupMessageSent($message))->toOthers();

            $users = ConversationUser::query()->where(['conversation_id' => $validated['conversation_id'], 'is_blocked' => 0])
                ->where('user_id', '<>', auth()->id())->pluck('user_id');

            User::whereIn('id', $users)->get()->each->notify(new MessageNotification($message));

            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
        }
    }

    protected function getAdminsAndChannels()
    {
        $roleUser = 'employee';
        $admins = User::whereHas('roles', function ($query) use ($roleUser) {
            $query->where('name', $roleUser);
        })->where('id', '!=', auth()->id())->get();

        $channels = Conversation::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id()); // Kiểm tra người dùng hiện tại có trong nhóm không
        })->get();
        return [
            'admins' => $admins,
            'channels' => $channels,
        ];
    }
    public function getGroupInfo(Request $request)
    {
        try {
            $groupId = $request->id;
            $group = Conversation::findOrFail($groupId);
            $name = $group->name;
            $memberCount = $group->users()->count() . ' thành viên';
            $member = $group->users()->select('user_id', 'name', 'avatar')->get();
            $leader = User::find($group->owner_id);
            $channelId = $group->id;
            // Trả về thông tin nhóm
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $name,  // Tên nhóm
                    'memberCount' => $memberCount, // Số thành viên
                    'member' => $member,
                    'group' => $group,
                    'leader' => $leader,
                    'avatar' => $avatar ?? url('assets/images/users/multi-user.jpg'),
                    'channelId' => $channelId,
                ]
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy thông tin nhóm'
            ]);
        }
    }

    public function getGroupMessages($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender', 'media') // Lấy thông tin người gửi
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages, 'id' => $conversationId]);
    }
    public function addMembersToGroup(Request $request)
    {
        $validated = $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id',  // Kiểm tra rằng các ID thành viên tồn tại trong bảng users 
        ]);

        // Lấy group_id và members
        $group = Conversation::find($request->group_id);
        $members = $request->members;
        $existingMembers = $group->users->pluck('id')->toArray();  // Lấy ID thành viên hiện tại của nhóm
        $newMembers = array_diff($members, $existingMembers);  // Lọc ra các thành viên chưa có trong nhóm
        // Thêm thành viên vào nhóm (giả sử nhóm có quan hệ many-to-many với users)
        foreach ($newMembers as $memberId) {
            $group->users()->attach($memberId);  // Thêm thành viên vào nhóm
        }

        return response()->json([
            'success' => true,
            'message' => 'Thành viên đã được thêm vào nhóm.',
        ]);
    }

    public function getSentFiles($conversationId)
    {
        try {
            $files = Media::whereHas('message', function ($query) use ($conversationId) {
                $query->where('conversation_id', $conversationId);
            })->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'files' => $files
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }
}
