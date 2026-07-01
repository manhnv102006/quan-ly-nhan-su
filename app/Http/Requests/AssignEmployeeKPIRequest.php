<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AssignEmployeeKPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\KPIAssignment|null $assignment */
        $assignment = $this->route('assignment');

        // Chỉ user là manager và đang thao tác trên KPI của chính mình mới được quyền
        return Auth::user()->isManager() && $assignment && $assignment->manager_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                // Đảm bảo nhân viên thuộc phòng ban của manager
                function ($attribute, $value, $fail) {
                    $managerDepartmentId = Auth::user()->employee?->department_id;
                    $employee = \App\Models\Employee::find($value);
                    if (! $managerDepartmentId || ! $employee || $employee->department_id !== $managerDepartmentId) {
                        $fail('Nhân viên được chọn không thuộc phòng ban của bạn.');
                    }
                },
            ],
            'target' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'deadline' => [
                'required',
                'date',
                'after_or_equal:today',
                // Không được vượt quá ngày kết thúc của KPI được giao cho Manager
                'before_or_equal:' . optional($this->route('assignment')?->end_date)->format('Y-m-d'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn một nhân viên.',
            'employee_id.exists' => 'Nhân viên được chọn không hợp lệ.',
            'target.required' => 'Vui lòng nhập tên mục tiêu.',
            'target.max' => 'Tên mục tiêu không được vượt quá 255 ký tự.',
            'comment.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'deadline.required' => 'Vui lòng nhập hạn chót cho nhân viên.',
            'deadline.after_or_equal' => 'Hạn chót phải từ hôm nay trở đi.',
            'deadline.before_or_equal' => 'Hạn chót không được vượt quá ngày kết thúc của KPI ('
                . optional($this->route('assignment')?->end_date)->format('d/m/Y') . ').',
        ];
    }
}