<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueLiveSessionCommand extends Command
{
    protected $signature = 'live-session:check-overdue';

    protected $description = 'Kiểm tra các buổi phát sóng trực tiếp đã quá hạn';

    public function handle()
    {
        $now =  Carbon::now();

        $overdueLiveSessions = LiveSession::query()
            ->whereIn('status', ['live', 'upcoming'])
            ->where('starts_at', '<', $now)
            ->whereNull('actual_start_time')
            ->get();

        if ($overdueLiveSessions->isEmpty()) {
            $this->info('Không có buổi phát sóng trực tiếp nào quá hạn.');
            return;
        }

        foreach ($overdueLiveSessions as $liveSession) {
            $liveSession->update([
                'status' => 'overdue',
            ]);
            $this->info('Cập nhật trạng thái buổi phát sóng trực tiếp: ' . $liveSession->title);
        }

        $this->info('Đã cập nhật trạng thái cho tất cả các buổi phát sóng trực tiếp quá hạn.');
    }
}
