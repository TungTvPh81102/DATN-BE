<?php

namespace App\Http\Controllers\Admin;

use App\Events\GroupMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chats\StoreGroupChatRequest;
use App\Http\Requests\Admin\Chats\StoreSendMessageRequest;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use App\Traits\LoggableTrait;
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerTrait;

class ChatController extends Controller
{
    use LoggableTrait;
    public function index()
    {
        $data = $this->getAdminsAndChannels();
        return view('chats.chat-realtime', $data);
    }
    public function createGroupChat(StoreGroupChatRequest $request)
    {
        try {
            $validated = $request->validated();

            // Tạo nhóm chat
            $conversation = Conversation::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'status' => '1',
                'conversationable_id' => null,
                'conversationable_type' => null,
            ]);

            // Thêm các thành viên vào nhóm chat
            foreach ($request->members as $member_id) {
                // Kiểm tra xem member có tồn tại không, nếu có thì attach vào nhóm
                $user = User::find($member_id);
                if ($user) {
                    $conversation->users()->attach($member_id);
                }
            }
            $data = $this->getAdminsAndChannels();
            $data['conversation'] = $conversation;

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm nhóm thành công',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return response()->json([
                'status' => 'error',
                'message' => 'Thao tác không thành công',
            ]);
        }
    }

    public function sendGroupMessage(StoreSendMessageRequest $request)
    {
        $validated = $request->validated();
        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'type' => $validated['type'],
            'meta_data' => $validated['meta_data'],
        ]);

        broadcast(new GroupMessageSent($message));

        $users = ConversationUser::query()->where(['conversation_id' => $validated['conversation_id'], 'is_blocked' => 0])
            ->where('user_id', '<>', auth()->id())->pluck('user_id');

        User::whereIn('id', $users)->get()->each->notify(new MessageNotification($message));

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    protected function getAdminsAndChannels()
    {
        $roleUser = 'admin';
        $admins = User::whereHas('roles', function ($query) use ($roleUser) {
            $query->where('name', $roleUser);
        })->get();

        $channels = Conversation::all();

        return [
            'admins' => $admins,
            'channels' => $channels
        ];
    }
    public function getGroupInfo(Request $request)
    {
        try {
            $groupId = $request->id;
            $group = Conversation::findOrFail($groupId);

            // Lấy số thành viên của nhóm
            $memberCount = $group->users()->count();

            // Trả về thông tin nhóm
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $group->name,  // Tên nhóm
                    'memberCount' => $memberCount . ' thành viên', // Số thành viên
                    'group' => $group,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể lấy thông tin nhóm'
            ]);
        }
    }
    public function getGroupMessages($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender') // Lấy thông tin người gửi
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages, 'id' => $conversationId]);
    }
}
