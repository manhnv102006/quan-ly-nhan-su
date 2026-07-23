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
        'leader_approved_by',
        'leader_approved_at',
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
        'leader_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaderApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_approved_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approverDisplayName(): ?string
    {
        return $this->actorDisplayName($this->approver);
    }

    public function rejecterDisplayName(): ?string
    {
        return $this->actorDisplayName($this->rejecter);
    }

    protected function actorDisplayName(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $user->loadMissing(['employee', 'role']);

        // Admin duyệt theo vai trò tài khoản, không lấy tên hồ sơ nhân viên đang liên kết.
        if ($user->isAdmin()) {
            return $user->name ?: 'Quản trị viên';
        }

        return $user->employee?->full_name ?: $user->name;
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
        'annual' => 'Nghỉ phép',
        'sick' => 'Nghỉ ốm',
        'unpaid' => 'Nghỉ không lương',
        'other' => 'Lý do khác',
    ];

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function needsLeaderApproval(): bool
    {
        return false;
    }

    public function isAwaitingLeaderApproval(): bool
    {
        return $this->isPending()
            && $this->needsLeaderApproval()
            && $this->leader_approved_at === null;
    }

    public function isAwaitingManagerApproval(): bool
    {
        return $this->isPending() && (
            ! $this->needsLeaderApproval() || $this->leader_approved_at !== null
        );
    }

    public function workflowStatusLabel(): string
    {
        if ($this->isAwaitingLeaderApproval()) {
            return 'Chờ Trưởng nhóm duyệt';
        }

        if ($this->isPending() && $this->leader_approved_at !== null) {
            return 'Chờ Quản lý duyệt';
        }

        return $this->statusLabel();
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
            abort(403, 'Bạn không có quyền xử lý đơn nghỉ phép này. Đơn không thuộc nhân viên do bạn quản lý.');
        }
    }

    public function scopeForLeader(Builder $query, Employee $leader): Builder
    {
        return $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->managedByLeader($leader));
    }

    public function scopeAwaitingLeaderApproval(Builder $query, Employee $leader): Builder
    {
        return $query
            ->forLeader($leader)
            ->where('status', self::STATUS_PENDING)
            ->whereNull('leader_approved_at')
            ->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->whereNotNull('manager_id'));
    }

    public function scopeAwaitingManagerApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
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
            ->when(! empty($filters['employee_name']), function (Builder $q) use ($filters) {
                $name = trim((string) $filters['employee_name']);
                $q->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('full_name', 'like', '%'.$name.'%'));
            })
            ->when(! empty($filters['employee_code']), function (Builder $q) use ($filters) {
                $code = trim((string) $filters['employee_code']);
                $q->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('employee_code', 'like', '%'.$code.'%'));
            })
            ->when(! empty($filters['leave_type']), fn (Builder $q) => $q->where('leave_type', $filters['leave_type']))
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['start_from']), fn (Builder $q) => $q->whereDate('start_date', '>=', $filters['start_from']))
            ->when(! empty($filters['start_to']), fn (Builder $q) => $q->whereDate('start_date', '<=', $filters['start_to']))
            ->when(! empty($filters['employee_id']), fn (Builder $q) => $q->where('employee_id', $filters['employee_id']))
            ->when(! empty($filters['department_id']), function (Builder $q) use ($filters) {
                $q->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $filters['department_id']));
            });
    }
}

