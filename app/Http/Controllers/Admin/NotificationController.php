<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use LoggableTrait, ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $countNotifications = $request->query('count_notifications', []);

            $typeGroups = [
                'approval' => [
                    'type' => ['register_course', 'register_instructor', "withdrawal"],
                    'count' => 10
                ],
                'message' => [
                    'type' => ['user_buy_course'],
                    'count' => 10
                ],
                'buycourse' => [
                    'type' => ['receive_message'],
                    'count' => 10
                ]
            ];

            if ($request->ajax() && $request->has('count_notifications') && !empty($countNotifications)) {
                foreach ($countNotifications as $key => $value) {
                    if (isset($typeGroups[$key])) {
                        $typeGroups[$key]['count'] = (int)$value['count'];
                    }
                }
            }

            $notifications = collect();

            foreach ($typeGroups as $key => $group) {
                $groupedNotifications = $user->notifications()
                    ->where(function ($query) use ($group) {
                        foreach ($group['type'] as $type) {
                            $query->orWhereJsonContains('data->type', $type);
                        }
                    })
                    ->latest()
                    ->take($group['count'] ?? 10)
                    ->get();
                $notifications = $notifications->merge($groupedNotifications);
            }

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            return $this->respondOk('Danh sách thông báo', [
                'notifications' => $notifications,
                'unread_notifications_count' => $unreadNotificationsCount
            ]);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function getUnreadNotificationsCount()
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            return $this->respondOk('Số thông báo chưa đọc', [
                'unread_notifications_count' => $unreadNotificationsCount,
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function markAsRead(string $notificationId, Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $notification = $user->notifications()->where('id', $notificationId)->first();

            if ($notification) {
                if (!$notification->read_at) {
                    $notification->markAsRead();
                }

                return $this->respondOk(
                    $notification->read_at ? 'Đánh dấu đã đọc thành công' : 'Đánh dấu chưa đọc thành công',
                );
            }

            return $this->respondError('Thông báo không tìm thấy');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }


    public function show(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $notification_key = $request->query('notification_key', 'approval');

            $typeGroups = [
                'approval' => [
                    'type' => ['register_course', 'register_instructor', "withdrawal"],
                ],
                'message' => [
                    'type' => ['user_buy_course'],
                ],
                'buycourse' => [
                    'type' => ['receive_message'],
                ]
            ];

            $queryNotifications = $user->notifications()
                ->where(function ($query) use ($notification_key, $typeGroups) {
                    foreach ($typeGroups[$notification_key]['type'] as $type) {
                        $query->orWhereJsonContains('data->type', $type);
                    }
                })
                ->latest();

            $status = $request->input('status', 'all');

            if ($status === 'unread') {
                $queryNotifications->whereNull('read_at');
            } else if ($status === 'read') {
                $queryNotifications->whereNotNull('read_at');
            }

            if ($request->has('query') && $request->input('query')) {
                $search = $request->input(key: 'query');
                $queryNotifications->where('data', 'like', "%$search%");
            }

            $notifications = $queryNotifications->with('notifiable')->latest()->paginate(10);

            if ($request->ajax()) {

                $html = view('notifications.table', compact('notifications'))->render();
                return response()->json(['html' => $html]);
            }


            return view('notifications.index', compact('notifications'));
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return $this->respondError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $notificationID = explode(',', $id);

                $this->deleteNotifications($notificationID);
            } else {
                $notification = DatabaseNotification::query()->findOrFail($id);

                $notification->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    private function deleteNotifications(array $notificationID)
    {

        DatabaseNotification::query()->whereIn('id', $notificationID)->delete();
    }

    
}
