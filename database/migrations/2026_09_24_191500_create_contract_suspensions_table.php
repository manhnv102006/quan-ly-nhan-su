<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_suspensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contract_id');
            $table->string('reason', 50);
            $table->date('start_date');
            $table->date('expected_end_date');
            $table->date('resumed_at')->nullable();
            $table->string('note', 1000)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->cascadeOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','pending','active','suspended','expired','replaced','terminated','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_suspensions');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','pending','active','expired','replaced','terminated','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
