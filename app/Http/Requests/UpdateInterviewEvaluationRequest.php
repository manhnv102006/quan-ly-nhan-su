<?php

namespace App\Http\Requests;

use App\Models\Interview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateInterviewEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        if (in_array($status, [Interview::STATUS_NO_SHOW, Interview::STATUS_SCHEDULED], true)) {
            $this->merge(Interview::normalizedEvaluationPayload($this->all()));

            return;
        }

        if ($status === Interview::STATUS_COMPLETED && $this->input('result') === 'failed') {
            $this->merge(['recommendation' => 'reject']);
        }
    }

    public function rules(): array
    {
        $status = $this->input('status');
        $result = $this->input('result');
        $requiresScores = Interview::evaluationScoresRequired($status, $result);

        $scoreRules = $requiresScores
            ? ['required', 'integer', 'between:0,10']
            : ['nullable', 'integer', 'between:0,10'];

        $resultRules = match ($status) {
            Interview::STATUS_SCHEDULED => ['nullable', 'in:pending'],
            Interview::STATUS_COMPLETED => ['required', 'in:passed,failed'],
            Interview::STATUS_NO_SHOW => ['nullable'],
            default => ['nullable', 'in:pending,passed,failed'],
        };

        $recommendationRules = match (true) {
            $status === Interview::STATUS_COMPLETED && $result === 'passed' => ['required', 'in:hire,consider'],
            $status === Interview::STATUS_COMPLETED && $result === 'failed' => ['nullable', 'in:reject'],
            default => ['nullable', 'in:hire,consider,reject'],
        };

        return [
            'status' => ['required', 'in:'.implode(',', Interview::EDITABLE_STATUSES)],
            'result' => $resultRules,
            'technical_score' => $scoreRules,
            'attitude_score' => $scoreRules,
            'culture_score' => $scoreRules,
            'overall_score' => $scoreRules,
            'recommendation' => $recommendationRules,
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $status = $this->input('status');

            if ($status === Interview::STATUS_SCHEDULED && $this->input('result') !== 'pending') {
                $validator->errors()->add('result', 'Khi trạng thái là Đã lên lịch, kết quả phải là Chờ kết quả.');
            }

            if ($status === Interview::STATUS_COMPLETED && ! in_array($this->input('result'), ['passed', 'failed'], true)) {
                $validator->errors()->add('result', 'Khi đã phỏng vấn, chỉ được chọn Đạt hoặc Không đạt.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái buổi phỏng vấn là bắt buộc.',
            'status.in' => 'Trạng thái buổi phỏng vấn không hợp lệ.',
            'result.required' => 'Kết quả phỏng vấn là bắt buộc.',
            'result.in' => 'Kết quả phỏng vấn không hợp lệ.',
            'recommendation.required' => 'Vui lòng chọn đề xuất tuyển dụng.',
            'recommendation.in' => 'Đề xuất tuyển dụng không hợp lệ.',
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
