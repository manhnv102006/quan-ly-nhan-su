<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamChatMessage extends Model
{
    public const TYPE_MESSAGE = 'message';

    public const TYPE_ANNOUNCEMENT = 'announcement';

    protected $fillable = [
        'team_leader_id',
        'sender_employee_id',
        'sender_user_id',
        'type',
        'title',
        'body',
    ];

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'team_leader_id');
    }

    public function senderEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sender_employee_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function isAnnouncement(): bool
    {
        return $this->type === self::TYPE_ANNOUNCEMENT;
    }

    public function senderDisplayName(): string
    {
        return $this->senderEmployee?->full_name
            ?? $this->senderUser?->name
            ?? 'Thành viên';
    }
}
