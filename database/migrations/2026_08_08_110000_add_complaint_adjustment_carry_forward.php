<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_complaints', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_complaints', 'confirmed_adjustment_amount')) {
                $table->decimal('confirmed_adjustment_amount', 15, 0)->nullable()->after('disputed_amount');
            }
            if (! Schema::hasColumn('payroll_complaints', 'carried_to_payroll_id')) {
                $table->foreignId('carried_to_payroll_id')->nullable()->after('resolved_at')->constrained('payrolls')->nullOnDelete();
            }
            if (! Schema::hasColumn('payroll_complaints', 'carried_at')) {
                $table->timestamp('carried_at')->nullable()->after('carried_to_payroll_id');
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('payrolls', 'complaint_adjustment')) {
                $table->decimal('complaint_adjustment', 15, 0)->default(0)->after('bonus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_complaints', 'carried_to_payroll_id')) {
                $table->dropForeign(['carried_to_payroll_id']);
                $table->dropColumn(['confirmed_adjustment_amount', 'carried_to_payroll_id', 'carried_at']);
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'complaint_adjustment')) {
                $table->dropColumn('complaint_adjustment');
            }
        });
    }
};
