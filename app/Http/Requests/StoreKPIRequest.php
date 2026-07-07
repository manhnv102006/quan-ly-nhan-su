<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKPIRequest extends FormRequest
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
            'positions.*' => 'in:manager',
            'target' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if ($this->input('unit') === '%') {
                        if (! is_numeric(str_replace(',', '.', (string) $value))) {
                            $fail('Mục tiêu phần trăm phải là số.');

                            return;
                        }

                        $numeric = (float) str_replace(',', '.', (string) $value);

                        if ($numeric < 0 || $numeric > 100) {
                            $fail('Mục tiêu phần trăm phải từ 0 đến 100.');
                        }

                        return;
                    }

                    if (strlen((string) $value) > 255) {
                        $fail('Mục tiêu không được vượt quá 255 ký tự.');
                    }
                },
            ],
            'unit' => 'nullable|string|max:50',
            'weight' => 'required|numeric|min:1|max:100',
            'max_score' => 'nullable|integer|min:1|max:1000',
            'period' => 'nullable|in:month,quarter,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'tasks' => 'nullable|array',
            'tasks.*.title' => 'nullable|string|max:255',
            'tasks.*.description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Danh sách nhiệm vụ đã được làm sạch (bỏ dòng trống, chuẩn hoá thứ tự).
     *
     * @return array<int, array{title: string, description: string|null, sort_order: int}>
     */
    public function cleanedTasks(): array
    {
        return collect($this->input('tasks', []))
            ->map(fn ($task) => [
                'title' => trim((string) ($task['title'] ?? '')),
                'description' => trim((string) ($task['description'] ?? '')) ?: null,
            ])
            ->filter(fn ($task) => $task['title'] !== '')
            ->values()
            ->map(fn ($task, $index) => [...$task, 'sort_order' => $index])
            ->all();
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
