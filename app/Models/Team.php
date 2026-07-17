<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'department_id',
        'name',
        'description',
        'leader_employee_id',
        'created_by',
        'status',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Thành viên nhóm (employees.manager_id = leader_employee_id).
     */
    public function members(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id', 'leader_employee_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_INACTIVE => 'Ngưng hoạt động',
            default => ucfirst($this->status),
        };
    }

    public function getStatusTailwindAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_INACTIVE => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}
