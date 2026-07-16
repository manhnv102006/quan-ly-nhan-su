<?php

namespace App\Http\Requests\Leader;

use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Services\LeaderScopeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AllocateTeamKpiRequest extends FormRequest
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
        /** @var KPIAssignment $assignment */
        $assignment = $this->route('assignment');

        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    $leader = app(LeaderScopeService::class)->resolveLeaderEmployee(Auth::user());
                    $employee = Employee::query()->find($value);

                    if (! $leader || ! $employee || ! app(LeaderScopeService::class)->managesEmployee($leader, $employee)) {
                        $fail('Nhân viên được chọn không thuộc nhóm của bạn.');
                    }
                },
            ],
            'target' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'deadline' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:'.$assignment->end_date->format('Y-m-d'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn thành viên.',
            'target.required' => 'Vui lòng nhập tên mục tiêu.',
            'deadline.required' => 'Vui lòng nhập hạn chót.',
            'deadline.before_or_equal' => 'Hạn chót không được vượt quá ngày kết thúc KPI nhóm.',
        ];
    }
}
