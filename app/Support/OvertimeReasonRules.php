<?php

namespace App\Support;

/**
 * Lý do / công việc tăng ca — bắt buộc để phục vụ giải trình với Sở LĐTBXH.
 */
final class OvertimeReasonRules
{
    public static function minLength(): int
    {
        return max(1, (int) config('overtime.min_reason_length', 10));
    }

    public static function maxLengthForEmployee(): int
    {
        return 500;
    }

    public static function maxLengthForAdmin(): int
    {
        return 1000;
    }

    /**
     * @return list<string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function rules(int $maxLength): array
    {
        return [
            'required',
            'string',
            'min:'.self::minLength(),
            'max:'.$maxLength,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        $min = self::minLength();

        return [
            'reason.required' => 'Vui lòng nhập lý do và công việc cần làm khi tăng ca.',
            'reason.min' => "Lý do/công việc phải có ít nhất {$min} ký tự để phục vụ giải trình với Sở LĐTBXH khi cần.",
            'reason.max' => 'Lý do/công việc không được vượt quá :max ký tự.',
        ];
    }
}
