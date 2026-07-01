<?php

namespace App\Http\Requests\Manager;

use App\Models\EmployeeKPI;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEmployeeKPIScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EmployeeKPI|null $employeeKpi */
        $employeeKpi = $this->route('employeeKpi');
        if (! $employeeKpi) {
            return false;
        }

        $employeeKpi->loadMissing('kpiAssignment');

        return $employeeKpi->kpiAssignment?->manager_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'review' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'Vui lòng nhập điểm KPI.',
            'score.integer' => 'Điểm KPI phải là số nguyên.',
            'score.min' => 'Điểm KPI không được nhỏ hơn 0.',
            'score.max' => 'Điểm KPI không được lớn hơn 100.',
            'review.max' => 'Review không được vượt quá 1000 ký tự.',
        ];
    }
}

