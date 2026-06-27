<?php

namespace App\Http\Requests;

class OvertimeRequestStoreRequest extends OvertimeRequestBaseRequest
{
    public function rules(): array
    {
        return $this->baseRules(false);
    }
}
