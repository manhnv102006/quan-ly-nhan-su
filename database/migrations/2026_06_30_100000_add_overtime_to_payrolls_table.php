<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('payrolls', 'overtime_hours')) {
                $table->decimal('overtime_hours', 8, 2)->default(0)->after('bonus');
            }
            if (! Schema::hasColumn('payrolls', 'overtime_pay')) {
                $table->decimal('overtime_pay', 15, 2)->default(0)->after('overtime_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'overtime_pay')) {
                $table->dropColumn('overtime_pay');
            }
            if (Schema::hasColumn('payrolls', 'overtime_hours')) {
                $table->dropColumn('overtime_hours');
            }
        });
    }
};
