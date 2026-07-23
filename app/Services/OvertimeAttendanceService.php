<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Support\TimeInput;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OvertimeAttendanceService
{
    /**
     * @return Collection<int, array{
     *     request: OvertimeRequest,
     *     start_label: string,
     *     end_label: string,
     *     can_check_in: bool,
     *     can_check_out: bool,
     *     status_message: string,
     *     status_tone: string,
     * }>
     */
    public function sessionsForDate(Employee $employee, Carbon $date, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now();

        return OvertimeRequest::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $date)
            ->whereIn('status', [
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_COMPLETED,
            ])
            ->orderBy('start_time')
            ->get()
            ->map(fn (OvertimeRequest $request) => $this->buildSession($request, $now));
    }

    public function checkIn(OvertimeRequest $overtimeRequest, Employee $employee): OvertimeRequest
    {
        $this->assertOwnership($overtimeRequest, $employee);

        $now = Carbon::now();
        [$windowStart, $windowEnd] = $this->window($overtimeRequest);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'overtime' => 'Chỉ chấm công tăng ca cho đơn đã được duyệt.',
            ]);
        }

        if (! $overtimeRequest->work_date?->isSameDay($now)) {
            throw ValidationException::withMessages([
                'overtime' => 'Chỉ chấm công tăng ca trong ngày đã đăng ký.',
            ]);
        }

        if ($overtimeRequest->actual_check_in) {
            throw ValidationException::withMessages([
                'overtime' => 'Bạn đã check-in khung tăng ca này.',
            ]);
        }

        if ($now->lt($windowStart) || $now->gt($windowEnd)) {
            throw ValidationException::withMessages([
                'overtime' => 'Chỉ check-in trong khung giờ tăng ca đã duyệt ('.$windowStart->format('H:i').' – '.$windowEnd->format('H:i').').',
            ]);
        }

        $overtimeRequest->update(['actual_check_in' => $now]);

        return $overtimeRequest->fresh();
    }

    public function checkOut(OvertimeRequest $overtimeRequest, Employee $employee): OvertimeRequest
    {
        $this->assertOwnership($overtimeRequest, $employee);

        $now = Carbon::now();
        [, $windowEnd] = $this->window($overtimeRequest);

        if ($overtimeRequest->status !== OvertimeRequest::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'overtime' => 'Khung tăng ca này không thể check-out.',
            ]);
        }

        if (! $overtimeRequest->actual_check_in) {
            throw ValidationException::withMessages([
                'overtime' => 'Bạn chưa check-in khung tăng ca này.',
            ]);
        }

        if ($overtimeRequest->actual_check_out) {
            throw ValidationException::withMessages([
                'overtime' => 'Bạn đã check-out khung tăng ca này.',
            ]);
        }

        $checkInAt = Carbon::parse($overtimeRequest->actual_check_in);
        if ($now->lte($checkInAt)) {
            throw ValidationException::withMessages([
                'overtime' => 'Giờ check-out phải sau giờ check-in ('.$checkInAt->format('H:i:s').').',
            ]);
        }

        if ($now->gt($windowEnd)) {
            throw ValidationException::withMessages([
                'overtime' => 'Đã quá khung giờ tăng ca ('.$windowEnd->format('H:i').').',
            ]);
        }

        return DB::transaction(function () use ($overtimeRequest, $now) {
            $actualHours = $this->calculateWorkedHours(
                $overtimeRequest,
                Carbon::parse($overtimeRequest->actual_check_in),
                $now,
            );

            $overtimeRequest->update([
                'actual_check_out' => $now,
                'total_hours' => $actualHours,
                'status' => OvertimeRequest::STATUS_COMPLETED,
            ]);

            return $overtimeRequest->fresh();
        });
    }

    /**
     * @return array{
     *     request: OvertimeRequest,
     *     start_label: string,
     *     end_label: string,
     *     can_check_in: bool,
     *     can_check_out: bool,
     *     status_message: string,
     *     status_tone: string,
     * }
     */
    public function buildSession(OvertimeRequest $request, Carbon $now): array
    {
        [$windowStart, $windowEnd] = $this->window($request);
        $startLabel = $windowStart->format('H:i');
        $endLabel = $windowEnd->format('H:i');

        if ($request->status === OvertimeRequest::STATUS_COMPLETED) {
            return [
                'request' => $request,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'can_check_in' => false,
                'can_check_out' => false,
                'status_message' => 'Đã hoàn thành chấm công tăng ca',
                'status_tone' => 'completed',
            ];
        }

        if ($request->actual_check_in && ! $request->actual_check_out) {
            $checkInAt = Carbon::parse($request->actual_check_in);
            $canCheckOut = $now->lte($windowEnd) && $now->gt($checkInAt);

            return [
                'request' => $request,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'can_check_in' => false,
                'can_check_out' => $canCheckOut,
                'status_message' => $canCheckOut
                    ? 'Đang trong ca tăng ca — hãy check-out trước '.$endLabel
                    : ($now->lte($checkInAt)
                        ? 'Check-out phải sau giờ check-in ('.$checkInAt->format('H:i:s').')'
                        : 'Đã quá giờ check-out ('.$endLabel.')'),
                'status_tone' => $canCheckOut ? 'active' : 'missed',
            ];
        }

        if ($now->lt($windowStart)) {
            return [
                'request' => $request,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'can_check_in' => false,
                'can_check_out' => false,
                'status_message' => 'Chưa đến giờ — mở lúc '.$startLabel,
                'status_tone' => 'upcoming',
            ];
        }

        if ($now->gt($windowEnd)) {
            return [
                'request' => $request,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'can_check_in' => false,
                'can_check_out' => false,
                'status_message' => 'Đã hết khung giờ tăng ca',
                'status_tone' => 'missed',
            ];
        }

        return [
            'request' => $request,
            'start_label' => $startLabel,
            'end_label' => $endLabel,
            'can_check_in' => true,
            'can_check_out' => false,
            'status_message' => 'Trong khung giờ — có thể check-in',
            'status_tone' => 'ready',
        ];
    }

    public function calculateWorkedHours(OvertimeRequest $request, Carbon $checkIn, Carbon $checkOut): float
    {
        [$windowStart, $windowEnd] = $this->window($request);

        $actualStart = $checkIn->greaterThan($windowStart) ? $checkIn : $windowStart;
        $actualEnd = $checkOut->lessThan($windowEnd) ? $checkOut : $windowEnd;

        if ($actualEnd->lte($actualStart)) {
            return 0;
        }

        $minutes = $actualStart->diffInMinutes($actualEnd, false);

        return round(max(0, $minutes) / 60, 2);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function window(OvertimeRequest $request): array
    {
        $workDate = Carbon::parse($request->work_date)->startOfDay();
        $start = Carbon::createFromFormat('H:i', TimeInput::forInput($request->start_time))->setDateFrom($workDate);
        $end = Carbon::createFromFormat('H:i', TimeInput::forInput($request->end_time))->setDateFrom($workDate);

        return [$start, $end];
    }

    protected function assertOwnership(OvertimeRequest $overtimeRequest, Employee $employee): void
    {
        if ((int) $overtimeRequest->employee_id !== (int) $employee->id) {
            throw ValidationException::withMessages([
                'overtime' => 'Bạn không có quyền chấm công đơn tăng ca này.',
            ]);
        }
    }
}
