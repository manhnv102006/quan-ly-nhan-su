<?php

namespace App\Http\Requests;

class OvertimeRequestUpdateRequest extends OvertimeRequestBaseRequest
{
    public function rules(): array
    {
        return $this->baseRules(true);
    }

    protected function ignoreOvertimeRequestId(): ?int
    {
        return $this->route('overtimeRequest')?->id;
    }
}
