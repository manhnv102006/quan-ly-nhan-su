<?php

namespace App\Http\Requests\Manager;

use App\Models\KPIAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssignKpiToLeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var KPIAssignment|null $assignment */
        $assignment = $this->route('assignment');

        return Auth::user()->isManager() && $assignment && $assignment->manager_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'leader_employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    $managerEmployee = Auth::user()->employee;
                    $leader = \App\Models\Employee::query()->with('user.role')->find($value);

                    if (! $managerEmployee || ! $leader) {
                        $fail('Trưởng nhóm không hợp lệ.');

                        return;
                    }

                    if ((int) $leader->department_id !== (int) $managerEmployee->department_id) {
                        $fail('Trưởng nhóm phải thuộc cùng phòng ban với bạn.');
                    }

                    if (! $leader->user?->isLeader()) {
                        $fail('Nhân viên được chọn không phải Trưởng nhóm.');
                    }
                },
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'leader_employee_id.required' => 'Vui lòng chọn Trưởng nhóm.',
        ];
    }
}
