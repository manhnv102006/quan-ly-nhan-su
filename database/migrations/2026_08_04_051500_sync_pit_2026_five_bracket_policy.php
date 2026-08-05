<?php

use App\Models\TaxPolicy;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        TaxPolicy::query()->updateOrCreate(
            ['code' => 'pit_2026'],
            [
                'name' => 'Luật TNCN 2025 — kỳ thuế 2026',
                'effective_from' => '2026-01-01',
                'effective_to' => null,
                'personal_deduction' => 15_500_000,
                'dependent_deduction_default' => 6_200_000,
                'brackets' => TaxPolicy::defaultBrackets2026(),
                'note' => 'Biểu lũy tiến 5 bậc: 5% / 10% / 20% / 30% / 35%. GT bản thân 15,5 triệu; NPT 6,2 triệu.',
            ]
        );
    }

    public function down(): void
    {
        // Giữ chính sách thuế đã áp dụng.
    }
};
