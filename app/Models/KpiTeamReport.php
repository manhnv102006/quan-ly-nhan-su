<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTeamReport extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'assignment_id',
        'leader_employee_id',
        'summary',
        'total_members',
        'completed_count',
        'avg_progress',
        'avg_leader_score',
        'status',
        'submitted_at',
        'manager_review',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'avg_progress' => 'decimal:2',
        'avg_leader_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(KPIAssignment::class, 'assignment_id');
    }

    public function leaderEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_employee_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_REJECTED], true);
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
