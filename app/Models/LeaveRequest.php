<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'reject_reason',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LeaveRequestHistory::class);
    }

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
    ];

    public const STATUS_BADGE_CLASSES = [
        self::STATUS_PENDING => 'text-bg-warning',
        self::STATUS_APPROVED => 'text-bg-success',
        self::STATUS_REJECTED => 'text-bg-danger',
    ];

    public const LEAVE_TYPE_LABELS = [
        'annual' => 'Nghỉ phép năm',
        'sick' => 'Nghỉ ốm',
        'unpaid' => 'Nghỉ không lương',
    ];

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'text-bg-secondary';
    }

    public function authorizeManagerAction(Employee $manager): void
    {
        $this->loadMissing('employee');

        if (! $this->employee?->isManagedBy($manager)) {
            abort(403, 'Bạn không có quyền duyệt yêu cầu này.');
        }
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeForManager(Builder $query, Employee $manager): Builder
    {
        return $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->managedByManager($manager));
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function (Builder $q) use ($filters) {
                $keyword = trim((string) $filters['search']);
                $q->whereHas('employee', function (Builder $employeeQuery) use ($keyword) {
                    $employeeQuery->where('full_name', 'like', '%'.$keyword.'%')
                        ->orWhere('employee_code', 'like', '%'.$keyword.'%');
                });
            })
            ->when(! empty($filters['leave_type']), fn (Builder $q) => $q->where('leave_type', $filters['leave_type']))
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['start_from']), fn (Builder $q) => $q->whereDate('start_date', '>=', $filters['start_from']))
            ->when(! empty($filters['start_to']), fn (Builder $q) => $q->whereDate('start_date', '<=', $filters['start_to']));
    }
}

