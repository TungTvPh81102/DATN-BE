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
use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerTrait;

class ChatController extends Controller
{
<<<<<<< HEAD
    use LoggableTrait;
    public function index()
    {
        $data = $this->getAdminsAndChannels();

        // dd($data);
        return view('chats.chat-realtime', $data);
=======
    use LoggableTrait, UploadToCloudinaryTrait;
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
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
    }
    public function createGroupChat(StoreGroupChatRequest $request)
    {
        try {
            $validated = $request->validated();
<<<<<<< HEAD

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
=======
            if (!isset($request->members) || !is_array($request->members) || count($request->members) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nhóm phải có ít nhất 3 thành viên (bao gồm người tạo nhóm).'
                ], 400);
            }
            // Tạo nhóm chat
            $conversation = Conversation::create([
                'name' => $validated['name'],
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
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
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
<<<<<<< HEAD
        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => auth()->id(),
            'parent_id' => $validated['parent_id'],
=======
        if ($request->hasFile('fileinput')) {
            $message['meta_data'] = $this->uploadImage($request->file('fileinput'), self::FOLDER);
        }
        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
            'content' => $validated['content'],
            'type' => $validated['type'],
            'meta_data' => $validated['meta_data'],
        ]);
<<<<<<< HEAD

        event(new MessageSent($message));

        return response()->json(['status' => 'success', 'message' => $message]);
    }
    protected function getAdminsAndChannels()
    {
        $roleUser = 'admin';

        $userID = auth()->id();

        $admins = User::whereHas('roles', function ($query) use ($roleUser) {
            $query->where('name', $roleUser);
        })->get();

        
        
        $channels = Conversation::all();

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

            // Lấy số thành viên của nhóm
            $memberCount = $group->users()->count();

            // Trả về thông tin nhóm
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => $group->name,  // Tên nhóm
                    'memberCount' => $memberCount . ' thành viên' // Số thành viên
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
            ->with('user') // Lấy thông tin người gửi
            ->latest()
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages]);
    }

    



=======
        // $media = Media::create(
        //     'file_path' => $validated
        //     'message_id' => $validated['message_id'],
        // );
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
        })->where('id', '!=', auth()->id())->get();

        $channels = Conversation::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id()); // Kiểm tra người dùng hiện tại có trong nhóm không
        })->get();

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
>>>>>>> 4ef90b1e0acaa21a00b3f01876bd103c76dec98d
}
