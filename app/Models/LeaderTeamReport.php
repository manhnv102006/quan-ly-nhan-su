<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderTeamReport extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'leader_employee_id',
        'manager_user_id',
        'period_month',
        'period_year',
        'title',
        'work_progress',
        'team_results',
        'notes',
        'member_count',
        'kpi_total',
        'kpi_completed',
        'avg_kpi_progress',
        'total_work_days',
        'total_late_days',
        'status',
        'submitted_at',
        'manager_review',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'avg_kpi_progress' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function leaderEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_employee_id');
    }

    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id')->withTrashed();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_REJECTED], true);
    }

    public function periodLabel(): string
    {
        return str_pad((string) $this->period_month, 2, '0', STR_PAD_LEFT).'/'.$this->period_year;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_SUBMITTED => 'Đã gửi Manager',
            self::STATUS_APPROVED => 'Manager đã duyệt',
            self::STATUS_REJECTED => 'Manager từ chối',
            default => ucfirst($this->status),
        };
    }

    public function getStatusTailwindAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-slate-100 text-slate-600 border-slate-200',
            self::STATUS_SUBMITTED => 'bg-sky-50 text-sky-700 border-sky-100',
            self::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}
