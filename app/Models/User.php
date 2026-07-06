<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ManagerEmployeeResolver;
use App\Services\ManagerScopeService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['role_id', 'username', 'name', 'email', 'password', 'status', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory */
    use HasFactory, Notifiable, SoftDeletes;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function managerEmployeeProfile(): ?Employee
    {
        return app(ManagerEmployeeResolver::class)->resolve($this);
    }

    /**
     * Danh sách nhân viên thuộc quyền quản lý (cấp dưới trực tiếp hoặc phòng ban được giao).
     */
    public function managedEmployeesQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        $manager = $this->managerEmployeeProfile();

        if (! $manager) {
            return null;
        }

        return app(ManagerScopeService::class)->managedEmployeesQuery($manager);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role && in_array($this->role->name, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(Role::MANAGER);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(Role::EMPLOYEE);
    }

    public function kpiAssignments()
    {
        return $this->hasMany(KPIAssignment::class, 'manager_id');
    }

    public function dashboardRouteName(): string
    {
        return $this->role?->dashboardRouteName() ?? 'dashboard';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Tài khoản chưa liên kết nhân viên (trừ nhân viên đang chỉnh sửa).
     *
     * @param  Builder<User>  $query
     */
    public function scopeAvailableForEmployeeLink(Builder $query, ?int $exceptEmployeeId = null): Builder
    {
        $linkedUserIds = Employee::query()
            ->whereNotNull('user_id')
            ->when($exceptEmployeeId, fn (Builder $employeeQuery) => $employeeQuery->where('id', '!=', $exceptEmployeeId))
            ->whereHas('user')
            ->pluck('user_id');

        return $query->whereNotIn('id', $linkedUserIds);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
