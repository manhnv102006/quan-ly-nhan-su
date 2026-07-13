<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTermination extends Model
{
    public const REASON_RESIGNATION = 'resignation';
    public const REASON_DISMISSAL = 'dismissal';
    public const REASON_EXPIRED_NO_RENEWAL = 'expired_no_renewal';
    public const REASON_RETIREMENT = 'retirement';
    public const REASON_MUTUAL_AGREEMENT = 'mutual_agreement';
    public const REASON_OTHER = 'other';

    public const REASON_LABELS = [
        self::REASON_RESIGNATION => 'Tự nghỉ việc',
        self::REASON_DISMISSAL => 'Sa thải / chấm dứt do vi phạm',
        self::REASON_EXPIRED_NO_RENEWAL => 'Hết hạn, không gia hạn',
        self::REASON_RETIREMENT => 'Nghỉ hưu',
        self::REASON_MUTUAL_AGREEMENT => 'Thỏa thuận hai bên',
        self::REASON_OTHER => 'Lý do khác',
    ];

    protected $fillable = [
        'contract_id',
        'reason',
        'end_date',
        'note',
        'file_path',
    ];

    protected $casts = [
        'end_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASON_LABELS[$this->reason] ?? ($this->reason ?: 'Không xác định');
    }
}
