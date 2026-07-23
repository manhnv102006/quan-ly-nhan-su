<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_allowances', function (Blueprint $table) {
            $table->string('allowance_name')->nullable()->after('amount');
            $table->string('allowance_code', 64)->nullable()->after('allowance_name');
            $table->string('calculation_type', 32)->nullable()->after('allowance_code');
            $table->text('calculation_note')->nullable()->after('calculation_type');
        });

        Schema::table('contract_histories', function (Blueprint $table) {
            $table->json('allowances_snapshot')->nullable()->after('changes');
        });

        if (Schema::hasTable('contract_allowances') && Schema::hasTable('allowance_types')) {
            DB::table('contract_allowances')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        if ($row->allowance_name) {
                            continue;
                        }
                        $type = DB::table('allowance_types')->where('id', $row->allowance_type_id)->first();
                        if (! $type) {
                            continue;
                        }
                        DB::table('contract_allowances')->where('id', $row->id)->update([
                            'allowance_name' => $type->name,
                            'allowance_code' => $type->code,
                            'calculation_type' => $type->calculation_type ?? null,
                            'calculation_note' => $type->calculation_note ?? null,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('contract_histories', function (Blueprint $table) {
            $table->dropColumn('allowances_snapshot');
        });

        Schema::table('contract_allowances', function (Blueprint $table) {
            $table->dropColumn(['allowance_name', 'allowance_code', 'calculation_type', 'calculation_note']);
        });
    }
};
