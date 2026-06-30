<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('delivery_status', ['scheduled', 'sent', 'failed'])->default('sent')->after('type');
            $table->timestamp('scheduled_at')->nullable()->after('delivery_status');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            $table->json('schedule_payload')->nullable()->after('sent_at');
        });

        DB::table('notifications')->whereNull('sent_at')->update([
            'sent_at' => DB::raw('created_at'),
            'delivery_status' => 'sent',
        ]);
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'scheduled_at', 'sent_at', 'schedule_payload']);
        });
    }
};
