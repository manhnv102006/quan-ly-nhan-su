<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    public const DEFAULT_MAX_EMPLOYEES = 10;

    public const MIN_MAX_EMPLOYEES = 1;

    public const MAX_MAX_EMPLOYEES = 100;

    protected $table = 'departments';

    protected $fillable = [
        'manager_id',
        'department_code',
        'department_name',
        'description',
        'max_employees',
        'status',
    ];

    protected $casts = [
        'max_employees' => 'integer',
    ];
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function employeeCount(?int $excludingEmployeeId = null): int
    {
        $query = $this->employees();

        if ($excludingEmployeeId) {
            $query->where('id', '!=', $excludingEmployeeId);
        }

        return $query->count();
    }

    public function maxEmployeesLimit(): int
    {
        return (int) ($this->max_employees ?: self::DEFAULT_MAX_EMPLOYEES);
    }

    public function hasEmployeeCapacity(?int $excludingEmployeeId = null): bool
    {
        return $this->employeeCount($excludingEmployeeId) < $this->maxEmployeesLimit();
    }

    public function remainingEmployeeCapacity(?int $excludingEmployeeId = null): int
    {
        return max(0, $this->maxEmployeesLimit() - $this->employeeCount($excludingEmployeeId));
    }

    public function isAtEmployeeCapacity(?int $excludingEmployeeId = null): bool
    {
        return ! $this->hasEmployeeCapacity($excludingEmployeeId);
    }
}