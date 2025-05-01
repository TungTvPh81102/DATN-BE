<?php

use App\Models\Conversation;
use App\Models\ConversationUser;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

//Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notification.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversationUser = ConversationUser::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->first();

    if ($conversationUser) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});

Broadcast::channel('private-chat.{conversationId}', function ($user, $conversationId) {
    $conversationUser = ConversationUser::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->first();

    if ($conversationUser) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});

Broadcast::channel('user-status', function ($user) {
    return $user ? ['id' => $user->id, 'name' => $user->name] : null;
});

Broadcast::channel('presence-live-session.{id}', function ($user, $id) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar
    ];
});
