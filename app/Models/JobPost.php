<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPost extends Model
{
    protected $table = 'job_posts';

    protected $fillable = [
        'department_id',
        'recruiter_id',
        'title',
        'quantity',
        'salary_min',
        'salary_max',
        'work_location',
        'work_type',
        'application_deadline',
        'description',
        'requirements',
        'benefits',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'application_deadline' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recruiter_id');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'open')
            ->where(function (Builder $query) {
                $query
                    ->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', now()->toDateString());
            });
    }
}
