<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('contract_types')
            ->where('contract_name', 'Hợp đồng thực tập')
            ->exists();

        if (! $exists) {
            DB::table('contract_types')->insert([
                'contract_name' => 'Hợp đồng thực tập',
                'duration_month' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('contract_types')
            ->where('contract_name', 'Hợp đồng thực tập')
            ->delete();
    }
};
