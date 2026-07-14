<?php

namespace App\Rules;

use App\Models\Department;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DepartmentEmployeeCapacity implements ValidationRule
{
    public function __construct(
        protected ?int $excludingEmployeeId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $department = Department::query()->find($value);

        if (! $department) {
            return;
        }

        if (! $department->hasEmployeeCapacity($this->excludingEmployeeId)) {
            $fail(sprintf(
                'Phòng ban "%s" đã đủ tối đa %d nhân viên.',
                $department->department_name,
                $department->maxEmployeesLimit(),
            ));
        }
    }
}
