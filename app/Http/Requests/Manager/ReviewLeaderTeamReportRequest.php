<?php

namespace App\Http\Requests\Manager;

use App\Models\LeaderTeamReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReviewLeaderTeamReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var LeaderTeamReport|null $report */
        $report = $this->route('report');

        return $report
            && Auth::user()->isManager()
            && (int) $report->manager_user_id === (int) Auth::id()
            && $report->status === LeaderTeamReport::STATUS_SUBMITTED;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'manager_review' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
