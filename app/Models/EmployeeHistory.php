<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHistory extends Model
{
    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_LINK_ACCOUNT = 'link_account';

    public const ACTION_UNLINK_ACCOUNT = 'unlink_account';

    public const ACTION_DELETE = 'delete';

    public const ACTION_RESTORE = 'restore';

    public const ACTION_FORCE_DELETE = 'force_delete';

    public const ACTION_LABELS = [
        self::ACTION_CREATE => 'Thêm nhân viên',
        self::ACTION_UPDATE => 'Sửa hồ sơ',
        self::ACTION_LINK_ACCOUNT => 'Liên kết tài khoản',
        self::ACTION_UNLINK_ACCOUNT => 'Gỡ liên kết',
        self::ACTION_DELETE => 'Xóa mềm',
        self::ACTION_RESTORE => 'Khôi phục',
        self::ACTION_FORCE_DELETE => 'Xóa vĩnh viễn',
    ];

    public const ACTION_BADGE_CLASSES = [
        self::ACTION_CREATE => 'bg-emerald-50 text-emerald-700',
        self::ACTION_UPDATE => 'bg-sky-50 text-sky-700',
        self::ACTION_LINK_ACCOUNT => 'bg-indigo-50 text-indigo-700',
        self::ACTION_UNLINK_ACCOUNT => 'bg-orange-50 text-orange-700',
        self::ACTION_DELETE => 'bg-amber-50 text-amber-700',
        self::ACTION_RESTORE => 'bg-lime-50 text-lime-700',
        self::ACTION_FORCE_DELETE => 'bg-red-50 text-red-800',
    ];

    protected $fillable = [
        'employee_id',
        'action',
        'summary',
        'changes',
        'note',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
