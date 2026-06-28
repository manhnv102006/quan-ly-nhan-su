<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:kpis,code,' . $this->route('kpi'),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:1|max:100',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã KPI là bắt buộc',
            'code.unique' => 'Mã KPI đã tồn tại',
            'title.required' => 'Tên KPI là bắt buộc',
            'weight.required' => 'Trọng số là bắt buộc',
            'weight.min' => 'Trọng số phải từ 1 trở lên',
            'weight.max' => 'Trọng số không được vượt quá 100',
            'department_id.required' => 'Phòng ban là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
        ];
    }
}
