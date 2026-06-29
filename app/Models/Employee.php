<?php

namespace App\Models;

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
    public function shifts()
{
    return $this->hasMany(EmployeeShift::class);
}
}
