<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldPersonal = 11_000_000;
        $newPersonal = 15_500_000;
        $oldDependent = 4_400_000;
        $newDependent = 6_200_000;

        DB::table('employee_tax_profiles')
            ->where('personal_deduction', $oldPersonal)
            ->update(['personal_deduction' => $newPersonal]);

        DB::table('tax_dependents')
            ->where('monthly_deduction', $oldDependent)
            ->update(['monthly_deduction' => $newDependent]);
    }

    public function down(): void
    {
        $oldPersonal = 11_000_000;
        $newPersonal = 15_500_000;
        $oldDependent = 4_400_000;
        $newDependent = 6_200_000;

        DB::table('employee_tax_profiles')
            ->where('personal_deduction', $newPersonal)
            ->update(['personal_deduction' => $oldPersonal]);

        DB::table('tax_dependents')
            ->where('monthly_deduction', $newDependent)
            ->update(['monthly_deduction' => $oldDependent]);
    }
};
