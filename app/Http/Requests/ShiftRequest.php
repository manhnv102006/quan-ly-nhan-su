<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['start_time', 'end_time'] as $field) {
            if ($this->has($field)) {
                $value = $this->normalizeTime((string) $this->input($field));
                if ($value !== null) {
                    $normalized[$field] = $value;
                }
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        $shiftId = $this->route('shift')?->id;

        return [
            'shift_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shifts', 'shift_name')->ignore($shiftId),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'shift_name.required' => 'Vui lòng nhập tên ca.',
            'shift_name.unique' => 'Tên ca đã tồn tại, vui lòng chọn tên khác.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu không hợp lệ.',
            'end_time.required' => 'Vui lòng chọn giờ kết thúc.',
            'end_time.date_format' => 'Giờ kết thúc không hợp lệ.',
            'end_time.different' => 'Giờ kết thúc phải khác giờ bắt đầu.',
        ];
    }

    private function normalizeTime(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return $value;
    }
}
