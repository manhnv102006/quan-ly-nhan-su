<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;

class EmployeeHistoryService
{
    public const FIELD_LABELS = [
        'employee_code' => 'Mã nhân viên',
        'full_name' => 'Họ và tên',
        'gender' => 'Giới tính',
        'date_of_birth' => 'Ngày sinh',
        'phone' => 'Số điện thoại',
        'email' => 'Email',
        'address' => 'Địa chỉ',
        'department_id' => 'Phòng ban',
        'position_id' => 'Chức vụ',
        'manager_id' => 'Quản lý trực tiếp',
        'hire_date' => 'Ngày vào làm',
        'status' => 'Trạng thái',
        'overtime_ban_status' => 'Cấm làm thêm giờ',
        'user_id' => 'Tài khoản liên kết',
    ];

    public const TRACKED_FIELDS = [
        'employee_code',
        'full_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'status',
        'overtime_ban_status',
        'user_id',
    ];

    public const GENDER_LABELS = [
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
    ];

    public function logCreate(Employee $employee, ?int $performedBy = null, ?string $note = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s thêm nhân viên %s (%s)',
            $this->performerName($performedBy),
            $employee->full_name,
            $employee->employee_code,
        );

        return $this->store($employee, EmployeeHistory::ACTION_CREATE, $summary, $performedBy, ['note' => $note]);
    }

    public function logUpdate(
        Employee $employee,
        array $changes,
        ?int $performedBy = null,
    ): ?EmployeeHistory {
        if ($changes === []) {
            return null;
        }

        $formatted = $this->formatChanges($changes);

        $summary = sprintf(
            '%s sửa hồ sơ nhân viên %s (%s)',
            $this->performerName($performedBy),
            $employee->full_name,
            $employee->employee_code,
        );

        return $this->store(
            $employee,
            EmployeeHistory::ACTION_UPDATE,
            $summary,
            $performedBy,
            ['changes' => $formatted],
        );
    }

    public function logLinkAccount(Employee $employee, User $user, ?int $performedBy = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s liên kết tài khoản %s với nhân viên %s',
            $this->performerName($performedBy),
            $user->username,
            $employee->full_name,
        );

        return $this->store(
            $employee,
            EmployeeHistory::ACTION_LINK_ACCOUNT,
            $summary,
            $performedBy,
            [
                'changes' => [[
                    'field' => 'user_id',
                    'label' => self::FIELD_LABELS['user_id'],
                    'old' => null,
                    'new' => $user->username,
                ]],
            ],
        );
    }

    public function logUnlinkAccount(Employee $employee, string $username, ?int $performedBy = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s gỡ liên kết tài khoản %s khỏi nhân viên %s',
            $this->performerName($performedBy),
            $username,
            $employee->full_name,
        );

        return $this->store(
            $employee,
            EmployeeHistory::ACTION_UNLINK_ACCOUNT,
            $summary,
            $performedBy,
            [
                'changes' => [[
                    'field' => 'user_id',
                    'label' => self::FIELD_LABELS['user_id'],
                    'old' => $username,
                    'new' => null,
                ]],
            ],
        );
    }

    public function logDelete(Employee $employee, ?int $performedBy = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s xóa mềm nhân viên %s (%s)',
            $this->performerName($performedBy),
            $employee->full_name,
            $employee->employee_code,
        );

        return $this->store($employee, EmployeeHistory::ACTION_DELETE, $summary, $performedBy);
    }

    public function logRestore(Employee $employee, ?int $performedBy = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s khôi phục nhân viên %s (%s)',
            $this->performerName($performedBy),
            $employee->full_name,
            $employee->employee_code,
        );

        return $this->store($employee, EmployeeHistory::ACTION_RESTORE, $summary, $performedBy);
    }

    public function logForceDelete(Employee $employee, ?int $performedBy = null): EmployeeHistory
    {
        $summary = sprintf(
            '%s xóa vĩnh viễn nhân viên %s (%s)',
            $this->performerName($performedBy),
            $employee->full_name,
            $employee->employee_code,
        );

        return $this->store($employee, EmployeeHistory::ACTION_FORCE_DELETE, $summary, $performedBy);
    }

    public function collectChanges(Employee $employee, array $original): array
    {
        $employee->refresh();
        $changes = [];

        foreach (self::TRACKED_FIELDS as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $employee->{$field};

            $normalizedOld = $this->normalizeValue($field, $oldValue);
            $normalizedNew = $this->normalizeValue($field, $newValue);

            if ($normalizedOld !== $normalizedNew) {
                $changes[$field] = [
                    'old' => $normalizedOld,
                    'new' => $normalizedNew,
                ];
            }
        }

        return $changes;
    }

    protected function store(
        Employee $employee,
        string $action,
        string $summary,
        ?int $performedBy,
        array $extra = [],
    ): EmployeeHistory {
        return EmployeeHistory::create([
            'employee_id' => $employee->id,
            'action' => $action,
            'summary' => $summary,
            'changes' => $extra['changes'] ?? null,
            'note' => $extra['note'] ?? null,
            'performed_by' => $performedBy,
        ]);
    }

    protected function performerName(?int $userId): string
    {
        if (! $userId) {
            return 'Hệ thống';
        }

        return User::query()->whereKey($userId)->value('name') ?? 'Hệ thống';
    }

    protected function normalizeValue(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array($field, ['date_of_birth', 'hire_date'], true)) {
            return Carbon::parse($value)->format('d/m/Y');
        }

        if ($field === 'gender') {
            return self::GENDER_LABELS[$value] ?? (string) $value;
        }

        if ($field === 'status') {
            return Employee::STATUS_LABELS[$value] ?? (string) $value;
        }

        if ($field === 'overtime_ban_status') {
            return Employee::OT_BAN_LABELS[$value] ?? (string) $value;
        }

        if ($field === 'department_id') {
            return Department::query()->whereKey($value)->value('department_name') ?? (string) $value;
        }

        if ($field === 'position_id') {
            return Position::query()->whereKey($value)->value('position_name') ?? (string) $value;
        }

        if ($field === 'manager_id') {
            return Employee::query()->whereKey($value)->value('full_name') ?? (string) $value;
        }

        if ($field === 'user_id') {
            return User::query()->whereKey($value)->value('username') ?? (string) $value;
        }

        return (string) $value;
    }

    protected function formatChanges(array $changes): array
    {
        $formatted = [];

        foreach ($changes as $field => $pair) {
            $formatted[] = [
                'field' => $field,
                'label' => self::FIELD_LABELS[$field] ?? $field,
                'old' => $pair['old'] ?? null,
                'new' => $pair['new'] ?? null,
            ];
        }

        return $formatted;
    }
}
