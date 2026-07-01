<?php

namespace App\Console\Commands;

use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class DispatchScheduledNotificationsCommand extends Command
{
    protected $signature = 'notifications:dispatch-scheduled';

    protected $description = 'Gửi các thông báo đã đến thời gian lên lịch';

    public function handle(AdminNotificationService $notifications): int
    {
        $result = $notifications->dispatchDueScheduledNotifications();

        $this->info("Đã gửi {$result['sent']} thông báo, {$result['failed']} thất bại.");

        return self::SUCCESS;
    }
}
