<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
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
            'hire_date'     => 'date',
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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
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

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function departmentTransfers(): HasMany
    {
        return $this->hasMany(DepartmentTransfer::class);
    }
}
