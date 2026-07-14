<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'previous_contract_id')) {
                $table->unsignedBigInteger('previous_contract_id')->nullable()->after('employee_id');
            }
            if (! Schema::hasColumn('contracts', 'renewal_count')) {
                $table->unsignedSmallInteger('renewal_count')->default(0)->after('contract_type_id');
            }
            if (! Schema::hasColumn('contracts', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('end_date');
            }
        });

        if (Schema::hasColumn('contracts', 'previous_contract_id')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->foreign('previous_contract_id')
                    ->references('id')
                    ->on('contracts')
                    ->nullOnDelete();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','pending','active','expired','replaced','terminated','cancelled') NOT NULL DEFAULT 'draft'");
        } else {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'previous_contract_id')) {
                $table->dropForeign(['previous_contract_id']);
                $table->dropColumn('previous_contract_id');
            }
            if (Schema::hasColumn('contracts', 'renewal_count')) {
                $table->dropColumn('renewal_count');
            }
            if (Schema::hasColumn('contracts', 'actual_end_date')) {
                $table->dropColumn('actual_end_date');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','active','expired','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
