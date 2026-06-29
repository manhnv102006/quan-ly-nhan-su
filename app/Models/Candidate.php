<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'job_post_id',
        'employee_id',
        'full_name',
        'phone',
        'email',
        'address',
        'birth_date',
        'cv_file',
        'status',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'converted_at' => 'datetime',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(RecruitmentEmailLog::class);
    }
}
