<?php

namespace App\Http\Requests;

use App\Models\OvertimeRequest;
use Illuminate\Validation\Rule;

class OvertimeRequestUpdateRequest extends OvertimeRequestBaseRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules(true);

        if ($this->user()?->isAdmin()) {
            $rules['work_date'] = ['required', 'date'];
            $rules['status'] = ['required', Rule::in([
                OvertimeRequest::STATUS_PENDING,
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_REJECTED,
                OvertimeRequest::STATUS_COMPLETED,
            ])];
            $rules['reject_reason'] = [
                Rule::requiredIf(fn () => $this->input('status') === OvertimeRequest::STATUS_REJECTED),
                'nullable',
                'string',
                'max:1000',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối khi chọn trạng thái Từ chối.',
        ]);
    }

    protected function ignoreOvertimeRequestId(): ?int
    {
        $model = $this->route('overtime_request') ?? $this->route('overtimeRequest');

        return $model?->id;
    }
}
