<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleChangeLog extends Model
{
    public const MODULE_INSURANCE = 'insurance';

    public const MODULE_TAX = 'tax';

    public const MODULE_PAYROLL = 'payroll';

    public const MODULE_ADVANCE = 'advance';

    public const MODULE_CONTRACT = 'contract';

    public const MODULE_LABELS = [
        self::MODULE_INSURANCE => 'Bảo hiểm',
        self::MODULE_TAX => 'Thuế TNCN',
        self::MODULE_PAYROLL => 'Quản lý lương',
        self::MODULE_ADVANCE => 'Tạm ứng lương',
        self::MODULE_CONTRACT => 'Hợp đồng',
    ];

    public const ACTION_LABELS = [
        'create' => 'Thêm mới',
        'update' => 'Cập nhật',
        'delete' => 'Xóa',
        'approve' => 'Duyệt',
        'reject' => 'Từ chối',
        'adjust' => 'Điều chỉnh',
        'stop' => 'Ngừng',
        'deduct' => 'Trừ vào lương',
        'calculate' => 'Tính lương',
        'recalculate' => 'Tính lại lương',
        'extend' => 'Gia hạn',
        'convert' => 'Chuyển loại',
        'cancel' => 'Hủy',
        'terminate' => 'Chấm dứt',
        'activate' => 'Kích hoạt',
    ];

    public const ACTION_BADGE_CLASSES = [
        'create' => 'bg-emerald-100 text-emerald-800',
        'update' => 'bg-sky-100 text-sky-800',
        'delete' => 'bg-rose-100 text-rose-800',
        'approve' => 'bg-teal-100 text-teal-800',
        'reject' => 'bg-rose-100 text-rose-800',
        'adjust' => 'bg-amber-100 text-amber-800',
        'stop' => 'bg-slate-100 text-slate-700',
        'deduct' => 'bg-violet-100 text-violet-800',
        'calculate' => 'bg-indigo-100 text-indigo-800',
        'recalculate' => 'bg-orange-100 text-orange-800',
    ];

    protected $fillable = [
        'module',
        'action',
        'entity_type',
        'entity_id',
        'employee_id',
        'field_name',
        'field_label',
        'old_value',
        'new_value',
        'note',
        'user_id',
        'user_name',
        'user_role',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moduleLabel(): string
    {
        return self::MODULE_LABELS[$this->module] ?? $this->module;
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function actionBadgeClass(): string
    {
        return self::ACTION_BADGE_CLASSES[$this->action] ?? 'bg-slate-100 text-slate-700';
    }
}
