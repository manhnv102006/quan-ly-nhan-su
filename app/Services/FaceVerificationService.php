<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FaceVerificationService
{
    /**
     * @return array{verified: bool, score: float, message: string}
     */
    public function verify(int $employeeId, string $imageBase64): array
    {
        $baseUrl = rtrim((string) config('services.face.api_url'), '/');

        if ($baseUrl === '') {
            return [
                'verified' => false,
                'score' => 0.0,
                'message' => 'Chưa cấu hình FACE_API_URL. Hãy chạy python face-service/api_server.py',
            ];
        }

        try {
            $response = Http::timeout(45)
                ->post("{$baseUrl}/verify", [
                    'employee_id' => $employeeId,
                    'image_base64' => $imageBase64,
                ]);
        } catch (ConnectionException) {
            return [
                'verified' => false,
                'score' => 0.0,
                'message' => 'Không kết nối được dịch vụ nhận diện khuôn mặt. Hãy chạy: python face-service/api_server.py',
            ];
        }

        $payload = $response->json() ?? [];

        return [
            'verified' => (bool) ($payload['verified'] ?? false),
            'score' => (float) ($payload['score'] ?? 0),
            'message' => (string) ($payload['message'] ?? 'Không xác thực được khuôn mặt.'),
        ];
    }
}
