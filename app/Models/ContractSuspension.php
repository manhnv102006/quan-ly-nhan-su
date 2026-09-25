<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSuspension extends Model
{
    public const REASON_MILITARY = 'military';

    public const REASON_DETENTION = 'detention';

    public const REASON_COMPULSORY = 'compulsory_measure';

    public const REASON_PREGNANCY = 'pregnancy';

    public const REASON_MUTUAL = 'mutual_agreement';

    public const REASON_OTHER = 'other';

    public const REASON_LABELS = [
        self::REASON_MILITARY => 'Nghĩa vụ quân sự / dân quân tự vệ',
        self::REASON_DETENTION => 'Tạm giữ, tạm giam',
        self::REASON_COMPULSORY => 'Trường giáo dưỡng, cai nghiện, giáo dục bắt buộc',
        self::REASON_PREGNANCY => 'Lao động nữ mang thai theo quy định',
        self::REASON_MUTUAL => 'Thỏa thuận hai bên',
        self::REASON_OTHER => 'Lý do khác',
    ];

    protected $fillable = [
        'contract_id',
        'reason',
        'start_date',
        'expected_end_date',
        'resumed_at',
        'note',
        'performed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'resumed_at' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASON_LABELS[$this->reason] ?? ($this->reason ?: 'Không xác định');
    }
}
