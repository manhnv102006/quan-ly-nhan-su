<?php

namespace Database\Seeders;

use App\Models\TaxPolicy;
use Illuminate\Database\Seeder;

class TaxPolicySeeder extends Seeder
{
    public function run(): void
    {
        TaxPolicy::query()->updateOrCreate(
            ['code' => 'pit_2013_2025'],
            [
                'name' => 'Biểu thuế 7 bậc (trước 2026)',
                'effective_from' => '2009-01-01',
                'effective_to' => '2025-12-31',
                'personal_deduction' => 11_000_000,
                'dependent_deduction_default' => 4_400_000,
                'brackets' => TaxPolicy::defaultBracketsLegacy7(),
                'note' => 'Mức giảm trừ và biểu thuế áp dụng đến hết kỳ thuế 2025.',
            ]
        );

        TaxPolicy::query()->updateOrCreate(
            ['code' => 'pit_2026'],
            [
                'name' => 'Luật TNCN 2025 — kỳ thuế 2026',
                'effective_from' => '2026-01-01',
                'effective_to' => null,
                'personal_deduction' => 15_500_000,
                'dependent_deduction_default' => 6_200_000,
                'brackets' => TaxPolicy::defaultBrackets2026(),
                'note' => 'Giảm trừ bản thân 15,5 triệu; NPT 6,2 triệu; biểu lũy tiến 5 bậc.',
            ]
        );
    }
}
