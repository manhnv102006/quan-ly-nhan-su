<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'manager_id',
        'employee_code',
        'full_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'avatar',
        'hire_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function employeeKpis(): HasMany
    {
        return $this->hasMany(EmployeeKPI::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function hasLinkedAccount(): bool
    {
        return $this->user_id !== null && $this->user !== null;
    }

    public function clearStaleUserLink(): bool
    {
        if ($this->user_id === null) {
            return false;
        }

        if (User::query()->whereKey($this->user_id)->exists()) {
            return false;
        }

        $this->forceFill(['user_id' => null])->save();

        return true;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function departmentTransfers(): HasMany
    {
        return $this->hasMany(DepartmentTransfer::class);
    }


    public static function managedDepartmentIdFor(self $manager): ?int
    {
        $ids = self::managedDepartmentIdsFor($manager);

        return $ids[0] ?? null;
    }

    /**
     * @return list<int>
     */
    public static function managedDepartmentIdsFor(self $manager): array
    {
        return Department::query()
            ->where('manager_id', $manager->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isManagedBy(self $manager): bool
    {
        if ($this->manager_id === $manager->id) {
            return true;
        }

        $managedDepartmentIds = self::managedDepartmentIdsFor($manager);

        return $managedDepartmentIds !== []
            && in_array((int) $this->department_id, $managedDepartmentIds, true);
    }

    /**
     * @param  Builder<Employee>  $query
     */
    public function scopeManagedByManager(Builder $query, self $manager): Builder
    {
        $managedDepartmentIds = self::managedDepartmentIdsFor($manager);

        return $query->where(function (Builder $scope) use ($manager, $managedDepartmentIds) {
            $scope->where('manager_id', $manager->id);

            if ($managedDepartmentIds !== []) {
                $scope->orWhereIn('department_id', $managedDepartmentIds);
            }
        });
    }

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }
   public function todayShift()
{
    return $this->employeeShifts()
        ->whereDate('work_date', today())
        ->with('shift')
        ->first();
}

}
