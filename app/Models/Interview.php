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
        return $status === 'no_show';
    }

    public static function evaluationScoresRequired(?string $status, ?string $result): bool
    {
        if (self::statusSkipsEvaluation($status)) {
            return false;
        }

        if ($status === 'completed') {
            return true;
        }

        return in_array($result, ['passed', 'failed'], true);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizedEvaluationPayload(array $validated): array
    {
        if (! self::statusSkipsEvaluation($validated['status'] ?? null)) {
            return $validated;
        }

        return array_merge($validated, [
            'result' => 'pending',
            'recommendation' => null,
            'technical_score' => null,
            'attitude_score' => null,
            'culture_score' => null,
            'overall_score' => null,
            'strengths' => null,
            'weaknesses' => null,
            'note' => null,
        ]);
    }
}
