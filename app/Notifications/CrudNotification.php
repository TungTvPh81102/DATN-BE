<?php

namespace App\Notifications;

use App\Models\User;
use App\Traits\LoggableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrudNotification extends Notification
{
    use Queueable,LoggableTrait;

    /**
     * Create a new notification instance.
     */
    protected $notifiableData = [];
    protected $userAction;

    public function __construct($notifiableData = [], $id)
    {
        $this->userAction = Auth::user();
        $this->notifiableData = $this->defaultData($notifiableData, $id);
    }

    protected function defaultData($notifiableData, $id)
    {
        $routeName = explode('.', request()->route()->getName());
        $action = $routeName[2] == 'store' ? 'thêm mới' : ($routeName[2] == 'update' ? 'cập nhật' : 'xóa');

        return array_merge([
            'title' => 'Thông báo hệ thống',
            'body' => 'Người dùng ' . $this->userAction->name . ' vừa thực hiện ' . $action . ' ' . $routeName[1] . ' có id là ' . $id,
            'nameuseraction' => $this->userAction->name,
            'avataruseraction' => $this->userAction->avatar,
            'controller' => $action,
            'method' => $action,
        ], $notifiableData);
    }

    public function toDatabase($notifiable)
    {
        return $this->notifiableData;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public static function sendToMany($data = [], $id)
    {
        $userReceivers = User::where('id', '<>', Auth::id())->get();

        try {

            foreach ($userReceivers as $userReceiver) {

                $userReceiver->notify(new CrudNotification($data, $id));
            }
        } catch (\Throwable $th) {
            
            Log::error(__CLASS__. '@' .__FUNCTION__.'line'. __LINE__, ['error' =>$th->getMessage()]);
        }
    }
}
