<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('related_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('action', 50);
            $table->text('summary');
            $table->json('changes')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'created_at']);
            $table->index(['contract_id', 'created_at']);
            $table->index('action');
        });

        if (Schema::hasTable('contract_activity_logs')) {
            $logs = DB::table('contract_activity_logs')
                ->join('contracts', 'contracts.id', '=', 'contract_activity_logs.contract_id')
                ->select([
                    'contract_activity_logs.contract_id',
                    'contracts.employee_id',
                    'contract_activity_logs.related_contract_id',
                    'contract_activity_logs.action',
                    'contract_activity_logs.description as summary',
                    'contract_activity_logs.performed_by',
                    'contract_activity_logs.created_at',
                    'contract_activity_logs.updated_at',
                ])
                ->get();

            foreach ($logs as $log) {
                DB::table('contract_histories')->insert([
                    'employee_id' => $log->employee_id,
                    'contract_id' => $log->contract_id,
                    'related_contract_id' => $log->related_contract_id,
                    'action' => $log->action,
                    'summary' => $log->summary,
                    'changes' => null,
                    'note' => null,
                    'performed_by' => $log->performed_by,
                    'created_at' => $log->created_at,
                    'updated_at' => $log->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_histories');
    }
};
