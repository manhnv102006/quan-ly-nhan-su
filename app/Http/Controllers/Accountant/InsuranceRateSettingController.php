<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\EmployeeInsurance;
use App\Models\InsuranceRateSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsuranceRateSettingController extends Controller
{
    public function index(): View
    {
        $setting = InsuranceRateSetting::current()->load('updater');

        return view('accountant.insurance.rates.index', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = InsuranceRateSetting::current();
        $limits = EmployeeInsurance::rateLimitsPercent();

        $validated = $request->validate([
            'bhxh_employee_rate' => 'required|numeric|min:0|max:'.$limits['bhxh_employee_rate']['max'],
            'bhxh_employer_rate' => 'required|numeric|min:0|max:'.$limits['bhxh_employer_rate']['max'],
            'bhyt_employee_rate' => 'required|numeric|min:0|max:'.$limits['bhyt_employee_rate']['max'],
            'bhyt_employer_rate' => 'required|numeric|min:0|max:'.$limits['bhyt_employer_rate']['max'],
            'bhtn_rate' => 'required|numeric|min:0|max:'.$limits['bhtn_rate']['max'],
            'note' => 'nullable|string|max:2000',
        ], $this->rateValidationMessages($limits));

        $bhtnDecimal = round((float) $validated['bhtn_rate'] / 100, 4);

        $setting->update([
            'bhxh_employee_rate' => round((float) $validated['bhxh_employee_rate'] / 100, 4),
            'bhxh_employer_rate' => round((float) $validated['bhxh_employer_rate'] / 100, 4),
            'bhyt_employee_rate' => round((float) $validated['bhyt_employee_rate'] / 100, 4),
            'bhyt_employer_rate' => round((float) $validated['bhyt_employer_rate'] / 100, 4),
            'bhtn_employee_rate' => $bhtnDecimal,
            'bhtn_employer_rate' => $bhtnDecimal,
            'note' => $validated['note'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('accountant.insurance.rates.index')
            ->with('success', 'Đã cập nhật tỷ lệ đóng bảo hiểm. Tỷ lệ mới áp dụng cho hồ sơ BH được tạo sau này.');
    }

    /**
     * @param  array<string, array{max: float, label: string}>  $limits
     * @return array<string, string>
     */
    private function rateValidationMessages(array $limits): array
    {
        $messages = [];

        foreach ($limits as $field => $config) {
            $messages["{$field}.max"] = "{$config['label']} không được vượt quá {$config['max']}%.";
            $messages["{$field}.required"] = "Tỷ lệ {$config['label']} là bắt buộc.";
        }

        return $messages;
    }
}
