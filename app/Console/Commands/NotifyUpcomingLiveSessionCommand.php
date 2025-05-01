<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Models\User;
use App\Notifications\UpcomingLiveSessionNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyUpcomingLiveSessionCommand extends Command
{
    protected $signature = 'live-session:notify-upcoming';

    protected $description = 'Gửi thông báo cho giảng viên về các buổi phát sóng sắp bắt đầu';

    public function handle()
    {
        $now = Carbon::now()->startOfMinute();
        $upcomingThreshold = (clone $now)->addMinutes(30);

        $upcomingLiveSessions = LiveSession::query()
            ->where('status', 'upcoming')
            ->whereNull('notified_at')
            ->whereBetween('starts_at', [$now, $upcomingThreshold])
            ->get();

        if ($upcomingLiveSessions->isEmpty()) {
            $this->info('Không có buổi phát sóng nào sắp bắt đầu.');
            return;
        }

        foreach ($upcomingLiveSessions as $liveSession) {
            $instructor = User::find($liveSession->instructor_id);

            Log::info('Gửi thông báo cho giảng viên: ' . $instructor->name . ' về buổi phát sóng: ' . $liveSession->title);

            if ($instructor) {
                $instructor->notify(new UpcomingLiveSessionNotification($liveSession));
                $liveSession->update(['notified_at' => now()]);

                $this->info('Đã gửi thông báo cho giảng viên: ' . $instructor->name . ' về buổi phát sóng: ' . $liveSession->title);
            }
        }

        $this->info('Hoàn thành gửi thông báo cho tất cả các buổi phát sóng sắp bắt đầu.');
    }
}
