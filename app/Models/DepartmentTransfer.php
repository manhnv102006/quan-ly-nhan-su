<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentTransfer extends Model
{
    protected $fillable = [
        'employee_id',
        'from_department_id',
        'to_department_id',
        'transferred_by',
        'effective_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function fromDepartmentName(): string
    {
        return $this->fromDepartment?->department_name ?? 'Chưa gán phòng ban';
    }

    public function toDepartmentName(): string
    {
        return $this->toDepartment?->department_name ?? '—';
    }

    public function performerDisplayName(): string
    {
        $user = $this->transferredBy;

        if (! $user) {
            return 'Hệ thống';
        }

        $user->loadMissing('employee');

        if ($user->isAdmin()) {
            return $user->name ?: 'Quản trị viên';
        }

        return $user->employee?->full_name ?: $user->name;
    }
}
