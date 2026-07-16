<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMembershipRequest extends Model
{
    public const ACTION_ADD = 'add';

    public const ACTION_REMOVE = 'remove';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const ACTION_LABELS = [
        self::ACTION_ADD => 'ThĂªm vĂ o nhĂ³m',
        self::ACTION_REMOVE => 'ÄÆ°a ra khá»i nhĂ³m',
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chá» duyá»‡t',
        self::STATUS_APPROVED => 'ÄĂ£ duyá»‡t',
        self::STATUS_REJECTED => 'Tá»« chá»‘i',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-100',
        self::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        self::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-100',
    ];

    protected $fillable = [
        'leader_id',
        'employee_id',
        'action',
        'reason',
        'status',
        'requested_by',
        'decided_by',
        'decided_at',
        'decision_note',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    /**
     * CĂ¡c Ä‘á» xuáº¥t thuá»™c phĂ²ng ban do quáº£n lĂ½ nĂ y phá»¥ trĂ¡ch (dá»±a trĂªn phĂ²ng ban cá»§a leader).
     *
     * @param  Builder<TeamMembershipRequest>  $query
     */
    public function scopeForManager(Builder $query, Employee $manager): Builder
    {
        $departmentIds = Employee::departmentIdsForManagerApproval($manager);

        return $query->whereHas('leader', function (Builder $leaderQuery) use ($departmentIds) {
            $leaderQuery->whereIn('department_id', $departmentIds);
        });
    }
}
