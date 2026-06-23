<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $table = 'interviews';

    protected $fillable = [
        'candidate_id',
        'interviewer_id',
        'interview_date',
        'result',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'datetime',
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
}
