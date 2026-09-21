<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestDocument extends Model
{
    protected $fillable = [
        'leave_request_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
