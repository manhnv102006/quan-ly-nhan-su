<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Services\OvertimeLimitService;
use Illuminate\Validation\Rule;

class OvertimeRequestStoreRequest extends OvertimeRequestBaseRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules(false);

        unset($rules['employee_id']);

        $rules['assignment_scope'] = ['required', Rule::in(['employee', 'department', 'company'])];
        $rules['employee_id'] = [
            Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'employee'),
            'required',
            'exists:employees,id',
        ];
        $rules['department_id'] = [
            Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'department'),
            'required',
            'exists:departments,id',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'assignment_scope.required' => 'Vui lòng chọn phạm vi tạo đơn.',
            'assignment_scope.in' => 'Phạm vi tạo đơn không hợp lệ.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không tồn tại.',
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');
            $workDate = $this->input('work_date');

            if (! $workDate || ! $start || ! $end) {
                return;
            }

            $employeeIds = $this->resolveEmployeeIds();

            if ($employeeIds === []) {
                $validator->errors()->add('assignment_scope', 'Không tìm thấy nhân viên phù hợp để tạo đơn tăng ca.');

                return;
            }

            $limitService = app(OvertimeLimitService::class);
            $newHours = $limitService->hoursBetween((string) $start, (string) $end);

            foreach ($employeeIds as $employeeId) {
                $exists = OvertimeRequest::query()
                    ->overlappingTime($employeeId, $workDate, $start, $end, $this->ignoreOvertimeRequestId())
                    ->exists();

                if ($exists) {
                    $name = Employee::query()->whereKey($employeeId)->value('full_name') ?? 'Nhân viên';
                    $validator->errors()->add(
                        'start_time',
                        "Khoảng thời gian tăng ca bị trùng trong cùng ngày ({$name})."
                    );

                    break;
                }

                $violations = $limitService->violations(
                    (int) $employeeId,
                    (string) $workDate,
                    $newHours,
                    $this->ignoreOvertimeRequestId(),
                );

                if ($violations !== []) {
                    $name = Employee::query()->whereKey($employeeId)->value('full_name') ?? 'Nhân viên';
                    $validator->errors()->add('start_time', $name.': '.reset($violations));

                    break;
                }
            }
        });
    }

    /**
     * @return list<int>
     */
    public function resolveEmployeeIds(): array
    {
        $scope = $this->input('assignment_scope', 'employee');

        return match ($scope) {
            'employee' => $this->filled('employee_id') ? [(int) $this->input('employee_id')] : [],
            'department' => $this->filled('department_id')
                ? Employee::query()
                    ->where('status', 'active')
                    ->where('department_id', $this->input('department_id'))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all()
                : [],
            'company' => Employee::query()
                ->where('status', 'active')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            default => [],
        };
    }
}
