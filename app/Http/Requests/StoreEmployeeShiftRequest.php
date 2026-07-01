<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $periodMode = $this->input('period_mode', 'single');

        return [
            'assignment_scope' => ['required', Rule::in(['employee', 'department', 'company'])],
            'period_mode' => ['required', Rule::in(['single', 'month', 'year', 'range'])],
            'shift_id' => ['required', 'exists:shifts,id'],
            'work_date' => [
                Rule::excludeIf(fn () => $periodMode !== 'single'),
                'required',
                'date',
            ],
            'work_month' => [
                Rule::excludeIf(fn () => $periodMode !== 'month'),
                'required',
                'date_format:Y-m',
            ],
            'work_year' => [
                Rule::excludeIf(fn () => $periodMode !== 'year'),
                'required',
                'integer',
                'min:2020',
                'max:2100',
            ],
            'start_date' => [
                Rule::excludeIf(fn () => $periodMode !== 'range'),
                'required',
                'date',
            ],
            'end_date' => [
                Rule::excludeIf(fn () => $periodMode !== 'range'),
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'employee_id' => [
                Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'employee'),
                'required',
                'exists:employees,id',
            ],
            'department_id' => [
                Rule::excludeIf(fn () => $this->input('assignment_scope') !== 'department'),
                'required',
                'exists:departments,id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('period_mode') !== 'range') {
                return;
            }

            $start = Carbon::parse($this->input('start_date'));
            $end = Carbon::parse($this->input('end_date'));
            $days = $start->diffInDays($end) + 1;

            if ($days > 366) {
                $validator->errors()->add('end_date', 'Khoảng gán ca tối đa 366 ngày.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'assignment_scope.required' => 'Vui lòng chọn phạm vi gán ca.',
            'assignment_scope.in' => 'Phạm vi gán ca không hợp lệ.',
            'period_mode.required' => 'Vui lòng chọn thời gian gán ca.',
            'period_mode.in' => 'Thời gian gán ca không hợp lệ.',
            'shift_id.required' => 'Vui lòng chọn ca làm.',
            'shift_id.exists' => 'Ca làm không tồn tại.',
            'work_date.required' => 'Vui lòng chọn ngày làm.',
            'work_date.date' => 'Ngày làm không hợp lệ.',
            'work_month.required' => 'Vui lòng chọn tháng gán ca.',
            'work_month.date_format' => 'Tháng gán ca phải đúng định dạng MM/YYYY.',
            'work_year.required' => 'Vui lòng chọn năm gán ca.',
            'work_year.integer' => 'Năm gán ca không hợp lệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không tồn tại.',
        ];
    }
}
