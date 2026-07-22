<?php

namespace App\Http\Requests;

use App\Models\Interview;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInterviewEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiresScores = Interview::evaluationScoresRequired(
            $this->input('status'),
            $this->input('result'),
        );

        $scoreRules = $requiresScores
            ? ['required', 'integer', 'between:0,10']
            : ['nullable', 'integer', 'between:0,10'];

        return [
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'result' => ['required', 'in:pending,passed,failed'],
            'technical_score' => $scoreRules,
            'attitude_score' => $scoreRules,
            'culture_score' => $scoreRules,
            'overall_score' => $scoreRules,
            'recommendation' => ['nullable', 'in:hire,consider,reject'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái buổi phỏng vấn là bắt buộc.',
            'status.in' => 'Trạng thái buổi phỏng vấn không hợp lệ.',
            'result.required' => 'Kết quả phỏng vấn là bắt buộc.',
            'result.in' => 'Kết quả phỏng vấn không hợp lệ.',
            'technical_score.required' => 'Vui lòng nhập điểm kỹ thuật.',
            'attitude_score.required' => 'Vui lòng nhập điểm thái độ.',
            'culture_score.required' => 'Vui lòng nhập điểm phù hợp văn hóa.',
            'overall_score.required' => 'Vui lòng nhập điểm tổng quan.',
            'technical_score.between' => 'Điểm kỹ thuật phải từ 0 đến 10.',
            'attitude_score.between' => 'Điểm thái độ phải từ 0 đến 10.',
            'culture_score.between' => 'Điểm phù hợp văn hóa phải từ 0 đến 10.',
            'overall_score.between' => 'Điểm tổng quan phải từ 0 đến 10.',
        ];
    }
}
