<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    /** @var list<string> */
    public const EVALUATION_SCORE_FIELDS = [
        'overall_score',
        'technical_score',
        'attitude_score',
        'culture_score',
    ];

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NO_SHOW = 'no_show';

    /** @var list<string> */
    public const EDITABLE_STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
    ];

    protected $table = 'interviews';

    protected $fillable = [
        'candidate_id',
        'interviewer_id',
        'interview_date',
        'status',
        'result',
        'technical_score',
        'attitude_score',
        'culture_score',
        'overall_score',
        'recommendation',
        'strengths',
        'weaknesses',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'datetime',
            'technical_score' => 'integer',
            'attitude_score' => 'integer',
            'culture_score' => 'integer',
            'overall_score' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'interviewer_id');
    }

    public static function statusSkipsEvaluation(?string $status): bool
    {
        return $status === self::STATUS_NO_SHOW;
    }

    public static function statusIsScheduled(?string $status): bool
    {
        return $status === self::STATUS_SCHEDULED;
    }

    public static function evaluationScoresRequired(?string $status, ?string $result): bool
    {
        if (self::statusSkipsEvaluation($status) || self::statusIsScheduled($status)) {
            return false;
        }

        return $status === self::STATUS_COMPLETED;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizedEvaluationPayload(array $validated): array
    {
        $status = $validated['status'] ?? null;

        if ($status === self::STATUS_NO_SHOW) {
            return array_merge($validated, [
                'result' => 'failed',
                'recommendation' => 'reject',
                'technical_score' => null,
                'attitude_score' => null,
                'culture_score' => null,
                'overall_score' => null,
                'strengths' => null,
                'weaknesses' => null,
            ]);
        }

        if ($status === self::STATUS_SCHEDULED) {
            return array_merge($validated, [
                'result' => 'pending',
                'recommendation' => null,
                'technical_score' => null,
                'attitude_score' => null,
                'culture_score' => null,
                'overall_score' => null,
                'strengths' => null,
                'weaknesses' => null,
            ]);
        }

        if (($validated['result'] ?? null) === 'failed') {
            $validated['recommendation'] = 'reject';
        }

        return $validated;
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Đã lên lịch',
            self::STATUS_COMPLETED => 'Đã phỏng vấn',
            self::STATUS_NO_SHOW => 'Không đến',
            'cancelled' => 'Đã hủy',
        ];
    }

    public static function resultLabels(): array
    {
        return [
            'pending' => 'Chờ kết quả',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
    }

    public static function recommendationLabels(): array
    {
        return [
            'hire' => 'Nên tuyển',
            'consider' => 'Cần cân nhắc',
            'reject' => 'Từ chối',
        ];
    }
}
