<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeFaceDescriptor;
use App\Services\EmployeeAttendanceService;
use App\Services\FaceMatchService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FaceController extends Controller
{
    public function __construct(
        private readonly EmployeeAttendanceService $attendanceService,
        private readonly NotificationService $notificationService,
        private readonly FaceMatchService $faceMatch,
    ) {
    }

    /**
     * Danh sách nhân viên đang làm việc (để công cụ đăng ký tra cứu theo mã).
     */
    public function employees(): JsonResponse
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code'])
            ->map(fn (Employee $employee) => [
                'employee_id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'face_enrolled' => $employee->faceDescriptors()->exists(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Danh sách khuôn mặt đã đăng ký, để kiosk đồng bộ về máy.
     */
    public function descriptors(): JsonResponse
    {
        $descriptors = EmployeeFaceDescriptor::query()
            ->with('employee:id,full_name,employee_code,status')
            ->get()
            ->filter(fn (EmployeeFaceDescriptor $descriptor) => $descriptor->employee
                && $descriptor->employee->status === 'active')
            ->map(fn (EmployeeFaceDescriptor $descriptor) => [
                'employee_id' => $descriptor->employee_id,
                'full_name' => $descriptor->employee->full_name,
                'employee_code' => $descriptor->employee->employee_code,
                'embedding' => $descriptor->embedding,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $descriptors,
        ]);
    }

    /**
     * Lưu một mẫu khuôn mặt (embedding) cho nhân viên.
     */
    public function storeDescriptor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'embedding' => ['required', 'array', 'size:512'],
            'embedding.*' => ['required', 'numeric'],
            'quality' => ['nullable', 'numeric'],
            'image_base64' => ['nullable', 'string'],
        ]);

        $conflict = $this->faceMatch->findConflictingDescriptor($data['embedding'], (int) $data['employee_id']);
        if ($conflict) {
            $owner = $conflict->employee;
            $ownerLabel = $owner
                ? trim($owner->full_name.' ('.$owner->employee_code.')')
                : 'một nhân viên khác';

            return response()->json([
                'success' => false,
                'message' => 'Khuôn mặt này đã được đăng ký cho '.$ownerLabel.'. Mỗi khuôn mặt chỉ được đăng ký cho một nhân viên.',
                'conflict_employee_id' => $conflict->employee_id,
            ], 422);
        }

        $imagePath = null;
        if (! empty($data['image_base64'])) {
            $imagePath = $this->storeSampleImage((int) $data['employee_id'], $data['image_base64']);
        }

        $descriptor = EmployeeFaceDescriptor::create([
            'employee_id' => $data['employee_id'],
            'embedding' => $data['embedding'],
            'quality' => $data['quality'] ?? null,
            'image_path' => $imagePath,
            'model_name' => config('services.face.model_name', 'buffalo_l'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu mẫu khuôn mặt.',
            'descriptor_id' => $descriptor->id,
        ], 201);
    }

    /**
     * Ghi nhận chấm công cho một nhân viên đã được nhận diện.
     */
    public function attendance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'action' => ['nullable', 'in:auto,check-in,check-out'],
            'confidence' => ['nullable', 'numeric'],
            'liveness_score' => ['nullable', 'numeric'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $now = Carbon::now();
        $today = Carbon::today();

        $todayShift = $employee->todayShift();
        if (! $todayShift || ! $todayShift->shift) {
            return response()->json([
                'success' => false,
                'message' => 'Nhân viên chưa được gán ca làm hôm nay.',
            ], 422);
        }

        $shift = $todayShift->shift;
        $isFullDay = $this->attendanceService->isFullDayShift($shift);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $requested = $data['action'] ?? 'auto';
        $action = $requested === 'auto'
            ? $this->decideAction($attendance, $isFullDay)
            : $requested;

        if ($action === 'done') {
            return response()->json([
                'success' => false,
                'message' => 'Nhân viên đã hoàn tất chấm công hôm nay.',
            ], 422);
        }

        $confidence = isset($data['confidence']) ? (float) $data['confidence'] : null;
        $livenessScore = isset($data['liveness_score']) ? (float) $data['liveness_score'] : null;

        try {
            if ($action === 'check-in') {
                $record = $this->attendanceService->checkIn($employee, $shift, $isFullDay, $now);
                $record->check_in_method = 'face';
                if ($confidence !== null) {
                    $record->recognition_confidence = $confidence;
                }
                if ($livenessScore !== null) {
                    $record->liveness_score = $livenessScore;
                }
                $record->save();

                $message = 'Chấm công vào thành công.';
            } else {
                if (! $attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nhân viên chưa chấm công vào hôm nay.',
                    ], 422);
                }

                $record = $this->attendanceService->checkOut($employee, $attendance, $isFullDay, $now);
                $record->check_out_method = 'face';
                if ($confidence !== null) {
                    $record->recognition_confidence = $confidence;
                }
                if ($livenessScore !== null) {
                    $record->liveness_score = $livenessScore;
                }
                $record->save();

                $message = 'Chấm công ra thành công.';
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        $this->notifyEmployee($employee, $action, $now);

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'employee' => [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
            ],
        ]);
    }

    /**
     * Gửi thông báo cho nhân viên sau khi chấm công bằng khuôn mặt.
     */
    private function notifyEmployee(Employee $employee, string $action, Carbon $now): void
    {
        if (! $employee->user_id) {
            return;
        }

        $label = $action === 'check-in' ? 'vào' : 'ra';
        $title = 'Chấm công bằng khuôn mặt';
        $content = "Bạn đã chấm công {$label} lúc {$now->format('H:i')} ngày {$now->format('d/m/Y')} bằng nhận diện khuôn mặt.";

        $this->notificationService->sendToUser($employee->user_id, $title, $content);
    }

    /**
     * Xác định hành động tiếp theo (check-in / check-out / done) cho chế độ auto.
     */
    private function decideAction(?Attendance $attendance, bool $isFullDay): string
    {
        if (! $attendance) {
            return 'check-in';
        }

        if ($isFullDay) {
            if (! $attendance->morning_check_in) {
                return 'check-in';
            }
            if (! $attendance->morning_check_out) {
                return 'check-out';
            }
            if (! $attendance->afternoon_check_in) {
                return 'check-in';
            }
            if (! $attendance->afternoon_check_out) {
                return 'check-out';
            }

            return 'done';
        }

        if (! $attendance->check_in) {
            return 'check-in';
        }
        if (! $attendance->check_out) {
            return 'check-out';
        }

        return 'done';
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
