<?php

namespace App\Http\Requests\Manager;

use App\Models\KpiTeamReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReviewKpiTeamReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var KpiTeamReport|null $report */
        $report = $this->route('report');

        if (! $report) {
            return false;
        }

        $report->loadMissing('assignment');

        return Auth::user()->isManager()
            && $report->assignment?->manager_id === Auth::id()
            && $report->status === KpiTeamReport::STATUS_SUBMITTED;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'manager_review' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
