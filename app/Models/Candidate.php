<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_PENDING_HIRE_APPROVAL = 'pending_hire_approval';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

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

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Mới',
            self::STATUS_INTERVIEW => 'Phỏng vấn',
            self::STATUS_PENDING_HIRE_APPROVAL => 'Chờ admin duyệt',
            self::STATUS_PASSED => 'Đạt',
            self::STATUS_FAILED => 'Không đạt',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function awaitsHireApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_HIRE_APPROVAL;
    }
}
