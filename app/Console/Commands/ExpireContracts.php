<?php

namespace App\Console\Commands;

use App\Services\ContractService;
use Illuminate\Console\Command;

class ExpireContracts extends Command
{
    protected $signature = 'contracts:auto-expire';

    protected $description = 'Tự động chuyển các hợp đồng hết hạn sang trạng thái expired';

    public function handle(ContractService $service): int
    {
        $count = $service->autoExpire();
        $this->info("Đã cập nhật {$count} hợp đồng hết hạn.");

        return Command::SUCCESS;
    }
}
