<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyLeaveRequestHistory extends Model
{
    protected $fillable = [
        'early_leave_request_id',
        'actor_id',
        'action',
        'note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function earlyLeaveRequest(): BelongsTo
    {
        return $this->belongsTo(EarlyLeaveRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
