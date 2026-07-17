<?php

namespace App\Http\Requests\Leader;

use App\Models\KPIAssignment;
use App\Services\LeaderScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitKpiTeamReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var KPIAssignment|null $assignment */
        $assignment = $this->route('assignment');

        if (! Auth::user()->isLeader() || ! $assignment) {
            return false;
        }

        $leader = app(LeaderScopeService::class)->resolveLeaderEmployee(Auth::user());

        return $leader && (int) $assignment->leader_employee_id === (int) $leader->id;
    }

    public function rules(): array
    {
        return [
            'summary' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'summary.required' => 'Vui lòng nhập tóm tắt báo cáo KPI nhóm.',
        ];
    }
}
