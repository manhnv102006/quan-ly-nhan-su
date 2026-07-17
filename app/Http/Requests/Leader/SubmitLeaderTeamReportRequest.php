<?php

namespace App\Http\Requests\Leader;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitLeaderTeamReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->isLeader() ?? false;
    }

    public function rules(): array
    {
        return [
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'work_progress' => ['required', 'string', 'max:10000'],
            'team_results' => ['required', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_progress.required' => 'Vui lòng nhập tiến độ công việc.',
            'team_results.required' => 'Vui lòng nhập kết quả nhóm.',
        ];
    }
}
