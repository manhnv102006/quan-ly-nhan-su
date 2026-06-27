<?php

namespace App\Console\Commands;

use App\Services\AutoNotificationService;
use Illuminate\Console\Command;

class NotifyExpiringContractsCommand extends Command
{
    protected $signature = 'notifications:contracts-expiring';

    protected $description = 'Gửi thông báo tự động cho hợp đồng sắp hết hạn';

    public function handle(AutoNotificationService $notifications): int
    {
        $sent = $notifications->notifyExpiringContracts();

        $this->info("Đã gửi {$sent} thông báo hợp đồng sắp hết hạn.");

        return self::SUCCESS;
    }
}
