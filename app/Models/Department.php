<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    public const DEFAULT_MAX_EMPLOYEES = 20;

    public const MIN_MAX_EMPLOYEES = 1;

    public const MAX_MAX_EMPLOYEES = 500;

    public const DEFAULT_MAX_MANAGERS = 1;

    public const MIN_MAX_MANAGERS = 1;

    public const MAX_MAX_MANAGERS = 50;

    protected $table = 'departments';

    protected $fillable = [
        'manager_id',
        'department_code',
        'department_name',
        'description',
        'max_employees',
        'max_managers',
        'status',
    ];

    protected $casts = [
        'max_employees' => 'integer',
        'max_managers' => 'integer',
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

    public function maxManagersLimit(): int
    {
        return (int) ($this->max_managers ?: self::DEFAULT_MAX_MANAGERS);
    }

    /**
     * @return list<int>
     */
    public function managerEmployeeIds(?int $excludingEmployeeId = null): array
    {
        $ids = $this->employees()
            ->whereHas('user.role', fn ($query) => $query->where('name', Role::MANAGER))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($this->manager_id && ! in_array((int) $this->manager_id, $ids, true)) {
            $ids[] = (int) $this->manager_id;
        }

        if ($excludingEmployeeId) {
            $ids = array_values(array_filter(
                $ids,
                fn (int $id) => $id !== (int) $excludingEmployeeId,
            ));
        }

        return $ids;
    }

    public function managerCount(?int $excludingEmployeeId = null): int
    {
        return count($this->managerEmployeeIds($excludingEmployeeId));
    }

    public function hasManagerCapacity(?int $excludingEmployeeId = null, ?int $includingEmployeeId = null): bool
    {
        $count = $this->managerCount($excludingEmployeeId);

        if ($includingEmployeeId && ! in_array((int) $includingEmployeeId, $this->managerEmployeeIds($excludingEmployeeId), true)) {
            $count++;
        }

        return $count < $this->maxManagersLimit();
    }

    public function remainingManagerCapacity(?int $excludingEmployeeId = null): int
    {
        return max(0, $this->maxManagersLimit() - $this->managerCount($excludingEmployeeId));
    }
}