<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeFaceDescriptor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class FaceEnrollmentService
{
    /**
     * @param  list<string>  $imageBase64Samples
     * @return array{descriptor_id: int, sample_count: int, message: string}
     */
    public function enroll(Employee $employee, array $imageBase64Samples): array
    {
        $baseUrl = rtrim((string) config('services.face.api_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Chưa cấu hình FACE_API_URL. Hãy chạy python face-service/api_server.py');
        }

        try {
            $response = Http::timeout(60)
                ->post("{$baseUrl}/enroll/extract-batch", [
                    'images' => array_values($imageBase64Samples),
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Không kết nối được dịch vụ nhận diện khuôn mặt. Hãy chạy: python face-service/api_server.py');
        }

        $payload = $response->json() ?? [];

        if ($response->status() === 404) {
            throw new RuntimeException('Face API chưa cập nhật. Hãy khởi động lại: python face-service/api_server.py');
        }

        if (! $response->successful()) {
            throw new RuntimeException((string) ($payload['message'] ?? "Face API lỗi HTTP {$response->status()}."));
        }

        if (! ($payload['success'] ?? false)) {
            throw new RuntimeException((string) ($payload['message'] ?? 'Không trích xuất được khuôn mặt.'));
        }

        $embedding = $payload['embedding'] ?? null;

        if (! is_array($embedding) || count($embedding) !== 512) {
            throw new RuntimeException('Dữ liệu khuôn mặt không hợp lệ từ dịch vụ nhận diện.');
        }

        $imagePath = null;
        $imageBase64 = $payload['image_base64'] ?? null;

        if (is_string($imageBase64) && $imageBase64 !== '') {
            $imagePath = $this->storeSampleImage($employee->id, $imageBase64);
        }

        $descriptor = EmployeeFaceDescriptor::create([
            'employee_id' => $employee->id,
            'embedding' => $embedding,
            'quality' => (float) ($payload['sample_count'] ?? count($imageBase64Samples)),
            'image_path' => $imagePath,
            'model_name' => config('services.face.model_name', 'buffalo_l'),
        ]);

        return [
            'descriptor_id' => $descriptor->id,
            'sample_count' => (int) ($payload['sample_count'] ?? count($imageBase64Samples)),
            'message' => (string) ($payload['message'] ?? 'Đã lưu mẫu khuôn mặt.'),
        ];
    }

    private function storeSampleImage(int $employeeId, string $base64): ?string
    {
        $payload = $base64;
        if (str_contains($payload, ',')) {
            $payload = substr($payload, strpos($payload, ',') + 1);
        }

        $binary = base64_decode($payload, true);
        if ($binary === false) {
            return null;
        }

        $path = "face-samples/{$employeeId}/".uniqid('face_', true).'.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
