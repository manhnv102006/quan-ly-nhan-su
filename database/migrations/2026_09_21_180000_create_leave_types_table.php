<?php

use App\Support\LeaveTypeDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();

            // Có trừ phép năm hay không: yes | no | compensatory | approver
            $table->string('annual_deduction', 20)->default('no');

            // Lương do ai trả: company | insurance | none | approver
            $table->string('salary_payer', 20)->default('company');
            $table->decimal('salary_percent', 5, 2)->nullable();

            // Hạn mức nghỉ
            $table->string('quota_type', 30)->default('none');
            $table->decimal('quota_days', 6, 1)->nullable();
            $table->string('quota_note', 255)->nullable();
            $table->boolean('enforce_quota')->default(false);

            // Giấy tờ minh chứng
            $table->boolean('requires_document')->default(false);
            $table->string('document_hint', 255)->nullable();

            $table->string('gender_restriction', 10)->nullable();
            $table->boolean('counts_as_leave')->default(true);
            $table->boolean('auto_generated')->default(false);
            $table->string('color', 20)->default('slate');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();

        foreach (LeaveTypeDefaults::rows() as $row) {
            DB::table('leave_types')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
