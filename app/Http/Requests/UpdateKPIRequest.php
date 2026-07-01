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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id',
            'positions' => 'nullable|array',
            'positions.*' => 'in:employee,leader,manager',
            'target' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'weight' => 'required|numeric|min:1|max:100',
            'max_score' => 'nullable|integer|min:1|max:1000',
            'period' => 'nullable|in:month,quarter,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tên KPI là bắt buộc',
            'departments.required' => 'Vui lòng chọn ít nhất một phòng ban áp dụng',
            'departments.min' => 'Vui lòng chọn ít nhất một phòng ban áp dụng',
            'departments.*.exists' => 'Phòng ban được chọn không hợp lệ',
            'positions.*.in' => 'Chức vụ áp dụng không hợp lệ',
            'weight.required' => 'Trọng số là bắt buộc',
            'weight.min' => 'Trọng số phải từ 1 trở lên',
            'weight.max' => 'Trọng số không được vượt quá 100',
            'max_score.integer' => 'Điểm tối đa phải là số nguyên',
            'period.in' => 'Kỳ đánh giá không hợp lệ',
            'end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu',
            'status.required' => 'Trạng thái là bắt buộc',
        ];
    }
}
