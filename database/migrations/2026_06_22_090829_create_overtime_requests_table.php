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
    Schema::create('overtime_requests', function (Blueprint $table) {

        $table->bigIncrements('id');

        $table->unsignedBigInteger('employee_id');

        $table->date('overtime_date');

        $table->time('start_time');

        $table->time('end_time');

        $table->decimal('total_hours', 5, 2)
            ->default(0);

        $table->enum(
            'overtime_type',
            [
                'weekday',
                'weekend',
                'holiday'
            ]
        )->default('weekday');

        $table->text('reason')
            ->nullable();

        $table->enum(
            'status',
            [
                'pending',
                'approved',
                'rejected'
            ]
        )->default('pending');

        $table->unsignedBigInteger('approved_by')
            ->nullable();

        $table->timestamp('approved_at')
            ->nullable();

        $table->text('manager_note')
            ->nullable();

        $table->timestamps();

        $table->foreign('employee_id')
            ->references('id')
            ->on('employees')
            ->cascadeOnDelete();

        $table->foreign('approved_by')
            ->references('id')
            ->on('employees')
            ->nullOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
