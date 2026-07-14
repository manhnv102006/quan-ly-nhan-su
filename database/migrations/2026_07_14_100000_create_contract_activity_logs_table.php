<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('related_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('action', 50);
            $table->text('description');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('contract_extensions', function (Blueprint $table) {
            if (! Schema::hasColumn('contract_extensions', 'new_contract_id')) {
                $table->foreignId('new_contract_id')->nullable()->after('contract_id')->constrained('contracts')->nullOnDelete();
            }
            if (! Schema::hasColumn('contract_extensions', 'performed_by')) {
                $table->foreignId('performed_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_extensions', function (Blueprint $table) {
            if (Schema::hasColumn('contract_extensions', 'new_contract_id')) {
                $table->dropForeign(['new_contract_id']);
                $table->dropColumn('new_contract_id');
            }
            if (Schema::hasColumn('contract_extensions', 'performed_by')) {
                $table->dropForeign(['performed_by']);
                $table->dropColumn('performed_by');
            }
        });

        Schema::dropIfExists('contract_activity_logs');
    }
};
