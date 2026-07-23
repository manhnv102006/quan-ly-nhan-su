<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractHistory extends Model
{
    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_EXTEND = 'extend';

    public const ACTION_CONVERT = 'convert';

    public const ACTION_CANCEL = 'cancel';

    public const ACTION_TERMINATE = 'terminate';

    public const ACTION_ACTIVATE = 'activate';

    public const ACTION_DELETE = 'delete';

    public const ACTION_RESTORE = 'restore';

    public const ACTION_FORCE_DELETE = 'force_delete';

    public const ACTION_LABELS = [
        self::ACTION_CREATE => 'Thêm hợp đồng',
        self::ACTION_UPDATE => 'Sửa hợp đồng',
        self::ACTION_EXTEND => 'Gia hạn',
        self::ACTION_CONVERT => 'Chuyển loại',
        self::ACTION_CANCEL => 'Hủy hợp đồng',
        self::ACTION_TERMINATE => 'Chấm dứt',
        self::ACTION_ACTIVATE => 'Kích hoạt',
        self::ACTION_DELETE => 'Xóa mềm',
        self::ACTION_RESTORE => 'Khôi phục',
        self::ACTION_FORCE_DELETE => 'Xóa vĩnh viễn',
    ];

    public const ACTION_BADGE_CLASSES = [
        self::ACTION_CREATE => 'bg-emerald-50 text-emerald-700',
        self::ACTION_UPDATE => 'bg-sky-50 text-sky-700',
        self::ACTION_EXTEND => 'bg-violet-50 text-violet-700',
        self::ACTION_CONVERT => 'bg-indigo-50 text-indigo-700',
        self::ACTION_CANCEL => 'bg-rose-50 text-rose-700',
        self::ACTION_TERMINATE => 'bg-rose-50 text-rose-800',
        self::ACTION_ACTIVATE => 'bg-teal-50 text-teal-700',
        self::ACTION_DELETE => 'bg-amber-50 text-amber-700',
        self::ACTION_RESTORE => 'bg-lime-50 text-lime-700',
        self::ACTION_FORCE_DELETE => 'bg-red-50 text-red-800',
    ];

    protected $fillable = [
        'employee_id',
        'contract_id',
        'related_contract_id',
        'action',
        'summary',
        'changes',
        'allowances_snapshot',
        'note',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'allowances_snapshot' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function relatedContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'related_contract_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function getActionBadgeClassAttribute(): string
    {
        return self::ACTION_BADGE_CLASSES[$this->action] ?? 'bg-slate-50 text-slate-700';
    }
}
