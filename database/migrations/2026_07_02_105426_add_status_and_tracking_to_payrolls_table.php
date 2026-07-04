<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'status')) {
                $table->enum('status', ['calculated', 'approved', 'paid', 'closed'])->default('calculated')->after('total_salary');
            }
            if (!Schema::hasColumn('payrolls', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payrolls', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('payrolls', 'paid_by')) {
                $table->unsignedBigInteger('paid_by')->nullable()->after('approved_at');
                $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payrolls', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'paid_by', 'paid_at']);
        });
    }
};
