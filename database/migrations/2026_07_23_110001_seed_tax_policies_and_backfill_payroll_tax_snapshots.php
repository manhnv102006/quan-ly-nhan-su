<?php

use App\Models\Payroll;
use App\Services\TaxService;
use Database\Seeders\TaxPolicySeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new TaxPolicySeeder)->run();

        $tax = app(TaxService::class);

        Payroll::query()
            ->with(['employee.taxProfile', 'employee.insurance', 'employee.taxDependents', 'payrollPeriod'])
            ->orderBy('id')
            ->chunkById(100, function ($payrolls) use ($tax) {
                foreach ($payrolls as $payroll) {
                    $tax->snapshotForPayroll($payroll);
                }
            });
    }

    public function down(): void
    {
        // Giữ snapshot & chính sách để không làm sai lịch sử đã chốt.
    }
};
