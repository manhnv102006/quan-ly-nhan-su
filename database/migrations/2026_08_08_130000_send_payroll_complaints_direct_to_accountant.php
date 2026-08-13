<?php

use App\Models\PayrollComplaint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PayrollComplaint::query()
            ->where('status', PayrollComplaint::STATUS_PENDING)
            ->update(['status' => PayrollComplaint::STATUS_PROCESSING]);
    }

    public function down(): void
    {
        // Không hoàn tác — khiếu nại đã chuyển thẳng kế toán.
    }
};
