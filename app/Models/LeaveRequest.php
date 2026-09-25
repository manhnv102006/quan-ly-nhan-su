<?php

namespace App\Models;

use App\Support\LeaveDateRange;
use App\Support\LeaveTypeRegistry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeaveRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const HALF_DAY_MORNING = 'morning';

    public const HALF_DAY_AFTERNOON = 'afternoon';

    public const HALF_DAY_PERIOD_LABELS = [
        self::HALF_DAY_MORNING => 'Buổi sáng',
        self::HALF_DAY_AFTERNOON => 'Buổi chiều',
    ];

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'half_day_period',
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
        'total_days' => 'float',
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

    public function document(): HasOne
    {
        return $this->hasOne(LeaveRequestDocument::class);
    }

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    public const STATUS_BADGE_CLASSES = [
        self::STATUS_PENDING => 'text-bg-warning',
        self::STATUS_APPROVED => 'text-bg-success',
        self::STATUS_REJECTED => 'text-bg-danger',
        self::STATUS_CANCELLED => 'text-bg-secondary',
    ];

    /**
     * Nhãn của mọi loại nghỉ phép, kể cả loại Admin đã tắt — đơn cũ vẫn cần hiển thị đúng tên.
     *
     * @return array<string, string>
     */
    public static function leaveTypeLabels(): array
    {
        return LeaveTypeRegistry::labels();
    }

    /** @return list<string> */
    public static function selectableLeaveTypes(): array
    {
        return LeaveTypeRegistry::codes();
    }

    /** @return list<string> */
    public static function selectableLeaveTypesForEmployee(?Employee $employee = null): array
    {
        return array_keys(self::leaveTypeLabelsForEmployee($employee));
    }

    /** @return array<string, string> */
    public static function leaveTypeLabelsForEmployee(?Employee $employee = null): array
    {
        return LeaveTypeRegistry::labelsForGender($employee?->gender);
    }

    /** @return list<string> */
    public static function paidLeaveTypes(): array
    {
        return LeaveTypeRegistry::paidCodes();
    }

    /** @return list<string> */
    public static function monthlyPaidQuotaLeaveTypes(): array
    {
        return LeaveTypeRegistry::monthlyPaidQuotaCodes();
    }

    /** @return list<string> */
    public static function annualDeductingLeaveTypes(): array
    {
        return LeaveTypeRegistry::annualDeductingCodes();
    }

    /** @return array<string, array{label: string, class: string}> */
    public static function leaveTypeBadgeMap(): array
    {
        return LeaveTypeRegistry::badgeMap();
    }

    public function leaveTypeConfig(): ?LeaveType
    {
        return LeaveTypeRegistry::find($this->leave_type);
    }

    public function leaveTypeLabel(): string
    {
        return $this->leaveTypeConfig()?->name ?? ucfirst((string) $this->leave_type);
    }

    public function halfDayPeriodLabel(): ?string
    {
        if ($this->leave_type !== 'half_day' || $this->half_day_period === null) {
            return null;
        }

        return self::HALF_DAY_PERIOD_LABELS[$this->half_day_period] ?? ucfirst((string) $this->half_day_period);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAwaitingManagerApproval(): bool
    {
        return $this->isPending();
    }

    public function workflowStatusLabel(): string
    {
        if ($this->isPending()) {
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
            throw \Illuminate\Validation\ValidationException::withMessages(['authorization' => 'Bạn không có quyền xử lý đơn nghỉ phép này. Đơn không thuộc nhân viên do bạn quản lý.']);
        }
    }

    public function scopeAwaitingManagerApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Khoảng nghỉ trùng periodStart–periodEnd: start <= periodEnd AND end >= periodStart.
     *
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeOverlappingPeriod(Builder $query, Carbon|string $periodStart, Carbon|string $periodEnd): Builder
    {
        $start = Carbon::parse($periodStart)->toDateString();
        $end = Carbon::parse($periodEnd)->toDateString();

        return $query->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId));
    }

    public function coversCalendarDay(Carbon|string $day): bool
    {
        return LeaveDateRange::dayWithinPeriod($day, $this->start_date, $this->end_date);
    }

    public static function periodsConflict(
        Carbon|string $aStart,
        Carbon|string $aEnd,
        ?string $aLeaveType,
        ?string $aHalfDayPeriod,
        Carbon|string $bStart,
        Carbon|string $bEnd,
        ?string $bLeaveType,
        ?string $bHalfDayPeriod,
    ): bool {
        if (! LeaveDateRange::periodsOverlap($aStart, $aEnd, $bStart, $bEnd)) {
            return false;
        }

        if (
            $aLeaveType === 'half_day'
            && $bLeaveType === 'half_day'
            && self::isSingleCalendarDay($aStart, $aEnd)
            && self::isSingleCalendarDay($bStart, $bEnd)
            && Carbon::parse($aStart)->isSameDay($bStart)
            && filled($aHalfDayPeriod)
            && filled($bHalfDayPeriod)
            && $aHalfDayPeriod !== $bHalfDayPeriod
        ) {
            return false;
        }

        return true;
    }

    public function conflictsWithSubmission(
        Carbon|string $startDate,
        Carbon|string $endDate,
        string $leaveType,
        ?string $halfDayPeriod = null,
    ): bool {
        return self::periodsConflict(
            $this->start_date,
            $this->end_date,
            $this->leave_type,
            $this->half_day_period,
            $startDate,
            $endDate,
            $leaveType,
            $halfDayPeriod,
        );
    }

    private static function isSingleCalendarDay(Carbon|string $start, Carbon|string $end): bool
    {
        return Carbon::parse($start)->isSameDay($end);
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

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeEmployeeListFilter(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            'active' => $query->where('status', self::STATUS_PENDING),
            'history' => $query->whereIn('status', [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED]),
            default => $query,
        };
    }
}

