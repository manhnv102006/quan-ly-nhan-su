<?php

namespace App\Models;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employeeKpis(): HasMany
    {
        return $this->hasMany(EmployeeKPI::class);
    }


    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return Department::query()
            ->where('manager_id', $manager->id)
            ->value('id');
    }

    public function isManagedBy(self $manager): bool
    {
        $managedDepartmentId = self::managedDepartmentIdFor($manager);

        return $this->manager_id === $manager->id
            || ($managedDepartmentId !== null && $this->department_id === $managedDepartmentId);
    }

    /**
     * Nhân viên thuộc quyền quản lý: cấp dưới trực tiếp hoặc cùng phòng ban được giao quản lý.
     *
     * @param  Builder<Employee>  $query
     */
    public function scopeManagedByManager(Builder $query, self $manager): Builder
    {
        $managedDepartmentId = self::managedDepartmentIdFor($manager);

        return $query->where(function (Builder $scope) use ($manager, $managedDepartmentId) {
            $scope->where('manager_id', $manager->id);

            if ($managedDepartmentId !== null) {
                $scope->orWhere('department_id', $managedDepartmentId);
            }
        });
    }
}
