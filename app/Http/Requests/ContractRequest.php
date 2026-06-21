<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contractId = $this->route('contract')?->id;

        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'contract_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('contracts', 'contract_code')->ignore($contractId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'expired', 'terminated'])],
            'signed_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Nhân viên là bắt buộc.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
            'contract_type_id.required' => 'Loại hợp đồng là bắt buộc.',
            'contract_type_id.exists' => 'Loại hợp đồng không hợp lệ.',
            'contract_code.required' => 'Mã hợp đồng là bắt buộc.',
            'contract_code.unique' => 'Mã hợp đồng đã tồn tại.',
            'contract_code.max' => 'Mã hợp đồng không được vượt quá 50 ký tự.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
            'salary.required' => 'Lương là bắt buộc.',
            'salary.numeric' => 'Lương phải là số.',
            'salary.min' => 'Lương phải lớn hơn hoặc bằng 0.',
            'status.required' => 'Trạng thái hợp đồng là bắt buộc.',
            'status.in' => 'Trạng thái hợp đồng không hợp lệ.',
            'contract_file.file' => 'Tệp hợp đồng không hợp lệ.',
            'contract_file.mimes' => 'Tệp hợp đồng phải là PDF, DOC hoặc DOCX.',
            'contract_file.max' => 'Tệp hợp đồng không được vượt quá 10MB.',
            'signed_date.date' => 'Ngày ký không hợp lệ.',
            'signed_date.after_or_equal' => 'Ngày ký phải bằng hoặc sau ngày bắt đầu.',
        ];
    }
}
