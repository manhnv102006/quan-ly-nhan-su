<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class JobPost extends Model
{
    protected $table = 'job_posts';

    protected $fillable = [
        'department_id',
        'recruiter_id',
        'submitted_by_employee_id',
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

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'submitted_by_employee_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'job_post_id');
    }

    public function scopePubliclyListed(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->publiclyListed();
    }

    public static function recordSuccessfulHire(?int $jobPostId): void
    {
        if (! $jobPostId) {
            return;
        }

        DB::transaction(function () use ($jobPostId) {
            $jobPost = self::query()->lockForUpdate()->find($jobPostId);

            if (! $jobPost) {
                return;
            }

            $quantity = max(0, $jobPost->quantity - 1);
            $attributes = ['quantity' => $quantity];

            if ($quantity === 0) {
                $attributes['status'] = 'closed';
            }

            $jobPost->update($attributes);
        });
    }

    public static function revertSuccessfulHire(?int $jobPostId): void
    {
        if (! $jobPostId) {
            return;
        }

        DB::transaction(function () use ($jobPostId) {
            $jobPost = self::query()->lockForUpdate()->find($jobPostId);

            if (! $jobPost) {
                return;
            }

            $wasClosedWithNoSlots = $jobPost->status === 'closed' && $jobPost->quantity === 0;

            $attributes = [
                'quantity' => $jobPost->quantity + 1,
            ];

            if ($wasClosedWithNoSlots) {
                $attributes['status'] = 'open';
            }

            $jobPost->update($attributes);
        });
    }
}
