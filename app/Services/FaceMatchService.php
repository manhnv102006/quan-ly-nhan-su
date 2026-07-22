<?php

namespace App\Services;

use App\Models\EmployeeFaceDescriptor;

class FaceMatchService
{
    /**
     * Ngưỡng cosine để coi hai khuôn mặt là cùng một người.
     * Trùng khớp với FACE_COSINE_THRESHOLD của dịch vụ nhận diện (python).
     */
    public function threshold(): float
    {
        return (float) config('services.face.cosine_threshold', 0.35);
    }

    /**
     * Cosine similarity giữa hai embedding.
     * Embedding lưu trong hệ thống đã được chuẩn hoá L2, nhưng vẫn tự chuẩn hoá
     * lại để an toàn khi dữ liệu đến từ nguồn khác.
     *
     * @param  list<float>  $a
     * @param  list<float>  $b
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b) || $a === []) {
            return -1.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $valueA) {
            $valueA = (float) $valueA;
            $valueB = (float) ($b[$i] ?? 0);

            $dot += $valueA * $valueB;
            $normA += $valueA * $valueA;
            $normB += $valueB * $valueB;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return -1.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Tìm khuôn mặt của nhân viên KHÁC trùng với embedding đưa vào.
     * Trả về descriptor xung đột (kèm quan hệ employee) hoặc null nếu không có.
     *
     * @param  list<float>  $embedding
     */
    public function findConflictingDescriptor(array $embedding, ?int $exceptEmployeeId = null): ?EmployeeFaceDescriptor
    {
        $threshold = $this->threshold();

        $best = null;
        $bestScore = $threshold;

        EmployeeFaceDescriptor::query()
            ->when($exceptEmployeeId !== null, fn ($query) => $query->where('employee_id', '!=', $exceptEmployeeId))
            ->with('employee:id,full_name,employee_code')
            ->chunk(200, function ($descriptors) use ($embedding, &$best, &$bestScore) {
                foreach ($descriptors as $descriptor) {
                    $stored = $descriptor->embedding;
                    if (! is_array($stored) || $stored === []) {
                        continue;
                    }

                    $score = $this->cosineSimilarity($embedding, $stored);
                    if ($score >= $bestScore) {
                        $bestScore = $score;
                        $best = $descriptor;
                    }
                }
            });

        return $best;
    }
}
