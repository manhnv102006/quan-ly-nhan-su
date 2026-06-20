<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_types', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('contract_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
