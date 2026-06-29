<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;

class LeaveRequestRejectRequest extends ApprovalRejectRequest
{
    public function authorize(): bool
    {
        $leaveRequest = $this->route('leaveRequest');

        return $leaveRequest instanceof LeaveRequest
            && $this->user()?->can('reject', $leaveRequest);
    }
}
