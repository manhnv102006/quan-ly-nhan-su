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

    public static function evaluationScoresRequired(?string $status, ?string $result): bool
    {
        if ($status === 'completed') {
            return true;
        }

        return in_array($result, ['passed', 'failed'], true);
    }
}
