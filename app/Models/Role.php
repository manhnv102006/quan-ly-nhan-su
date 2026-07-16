<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const ADMIN = 'admin';

    public const MANAGER = 'manager';

    public const EMPLOYEE = 'employee';

    public const ACCOUNTANT = 'accountant';

    public const LEADER = 'leader';

    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function label(): string
    {
        return match ($this->name) {
            self::ADMIN => 'Quản trị viên',
            self::MANAGER => 'Quản lý',
            self::EMPLOYEE => 'Nhân viên',
            self::ACCOUNTANT => 'Kế toán',
            self::LEADER => 'Trưởng nhóm',
            default => $this->name,
        };
    }

    public function dashboardRouteName(): string
    {
        return match ($this->name) {
            self::ADMIN => 'admin.dashboard',
            self::MANAGER => 'manager.dashboard',
            self::EMPLOYEE => 'employee.dashboard',
            self::ACCOUNTANT => 'accountant.dashboard',
            self::LEADER => 'leader.dashboard',
            default => 'dashboard',
        };
    }
}
