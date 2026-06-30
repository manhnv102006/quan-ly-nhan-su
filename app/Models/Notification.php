<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'sender_id',
        'type',
        'department_id',
        'delivery_status',
        'scheduled_at',
        'sent_at',
        'schedule_payload',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'schedule_payload' => 'array',
        ];
    }

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_users')
            ->withPivot(['is_read', 'read_at'])
            ->withTimestamps();
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'leave' => 'Nghỉ phép',
            'payroll' => 'Lương',
            'kpi' => 'KPI',
            default => 'Hệ thống',
        };
    }
}
