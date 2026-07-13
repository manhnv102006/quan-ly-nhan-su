<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_types', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->unique()->after('id');
            $table->string('category', 30)->default('fixed')->after('contract_name');
            $table->text('description')->nullable()->after('duration_month');
        });

        Schema::table('contract_terminations', function (Blueprint $table) {
            $table->string('reason', 50)->nullable()->after('contract_id');
        });
    }

    public function down(): void
    {
        Schema::table('contract_terminations', function (Blueprint $table) {
            $table->dropColumn('reason');
        });

        Schema::table('contract_types', function (Blueprint $table) {
            $table->dropColumn(['code', 'category', 'description']);
        });
    }
};
