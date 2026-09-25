<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveCancellationService
{
    public function __construct(
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly LeaveCarryOverService $leaveCarryOverService,
    ) {}

    /**
     * @return array{
     *     mode: string,
     *     message: string,
     *     refunded_days: float,
     *     kept_days: float,
     *     new_end_date: ?string
     * }
     */
    public function plan(LeaveRequest $leaveRequest, ?Carbon $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();

        if ($leaveRequest->status === LeaveRequest::STATUS_PENDING) {
            if ($this->leaveHasStarted($leaveRequest, $today)) {
                return $this->planResult(
                    'blocked',
                    'Đơn đã đến ngày nghỉ, không thể hủy.',
                    0.0,
                    (float) $leaveRequest->total_days,
                    null,
                );
            }

            return $this->planResult(
                'full',
                'Hủy đơn đang chờ duyệt. Toàn bộ số ngày đang giữ chỗ được hoàn lại.',
                (float) $leaveRequest->total_days,
                0.0,
                null,
            );
        }

        if ($leaveRequest->status !== LeaveRequest::STATUS_APPROVED) {
            return $this->planResult(
                'blocked',
                'Chỉ hủy được đơn đang chờ duyệt hoặc đã duyệt.',
                0.0,
                0.0,
                null,
            );
        }

        $workingDays = $this->workingDates($leaveRequest);
        $consumed = array_values(array_filter(
            $workingDays,
            fn (string $day) => Carbon::parse($day)->startOfDay()->lte($today),
        ));

        if ($consumed !== []) {
            return $this->planResult(
                'blocked',
                'Bạn đã nghỉ rồi, không thể hủy đơn này.',
                0.0,
                $this->daysForDates($leaveRequest, $consumed),
                null,
            );
        }

        return $this->planResult(
            'full',
            'Hủy đơn đã duyệt trước ngày nghỉ. Toàn bộ số ngày được hoàn vào số dư.',
            (float) $leaveRequest->total_days,
            0.0,
            null,
        );
    }

    private function leaveHasStarted(LeaveRequest $leaveRequest, Carbon $today): bool
    {
        $start = Carbon::parse($leaveRequest->start_date)->startOfDay();

        return $start->lte($today);
    }

    public function canCancel(LeaveRequest $leaveRequest, ?Carbon $today = null): bool
    {
        return $this->plan($leaveRequest, $today)['mode'] !== 'blocked';
    }

    public function cancel(LeaveRequest $leaveRequest, int $actorId, ?Carbon $today = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $actorId, $today) {
            Employee::query()->whereKey($leaveRequest->employee_id)->lockForUpdate()->first();
            $leaveRequest->refresh();

            $plan = $this->plan($leaveRequest, $today);

            if ($plan['mode'] === 'blocked') {
                throw ValidationException::withMessages(['leave_request' => $plan['message']]);
            }

            if ($plan['mode'] === 'partial') {
                return $this->cancelRemainder($leaveRequest, $actorId, $plan);
            }

            return $this->cancelEntireRequest($leaveRequest, $actorId, $plan);
        });
    }

    /**
     * @param  array{mode: string, message: string, refunded_days: float, kept_days: float, new_end_date: ?string}  $plan
     */
    private function cancelEntireRequest(LeaveRequest $leaveRequest, int $actorId, array $plan): LeaveRequest
    {
        if ($leaveRequest->status === LeaveRequest::STATUS_APPROVED) {
            $this->leaveCarryOverService->releaseForReducedLeave($leaveRequest, 0.0);
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_CANCELLED,
        ]);

        LeaveRequestHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $actorId,
            'action' => 'cancelled',
            'note' => $plan['message'],
        ]);

        return $leaveRequest->fresh();
    }

    /**
     * @param  array{mode: string, message: string, refunded_days: float, kept_days: float, new_end_date: ?string}  $plan
     */
    private function cancelRemainder(LeaveRequest $leaveRequest, int $actorId, array $plan): LeaveRequest
    {
        $previousEnd = $leaveRequest->end_date?->format('d/m/Y');
        $this->leaveCarryOverService->releaseForReducedLeave($leaveRequest, $plan['kept_days']);

        $leaveRequest->update([
            'end_date' => $plan['new_end_date'],
            'total_days' => $plan['kept_days'],
        ]);

        LeaveRequestHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $actorId,
            'action' => 'cancelled',
            'note' => sprintf(
                '%s Kỳ nghỉ rút từ %s xuống %s.',
                $plan['message'],
                $previousEnd,
                Carbon::parse($plan['new_end_date'])->format('d/m/Y'),
            ),
        ]);

        return $leaveRequest->fresh();
    }

    /**
     * @return list<string>
     */
    private function workingDates(LeaveRequest $leaveRequest): array
    {
        $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $end = Carbon::parse($leaveRequest->end_date)->startOfDay();
        $holidays = Holiday::inRange($start->toDateString(), $end->toDateString())->get();

        if ($leaveRequest->leave_type === 'half_day') {
            $dates = $this->leaveBalanceService->workingDayDatesInRange($start, $start, $holidays);

            return $dates === [] ? [] : [$dates[0]];
        }

        return $this->leaveBalanceService->workingDayDatesInRange($start, $end, $holidays);
    }

    /**
     * @param  list<string>  $dates
     */
    private function daysForDates(LeaveRequest $leaveRequest, array $dates): float
    {
        if ($dates === []) {
            return 0.0;
        }

        if ($leaveRequest->leave_type === 'half_day') {
            return 0.5;
        }

        return (float) count($dates);
    }

    /**
     * @return array{mode: string, message: string, refunded_days: float, kept_days: float, new_end_date: ?string}
     */
    private function planResult(string $mode, string $message, float $refunded, float $kept, ?string $newEnd): array
    {
        return [
            'mode' => $mode,
            'message' => $message,
            'refunded_days' => $refunded,
            'kept_days' => $kept,
            'new_end_date' => $newEnd,
        ];
    }

    private function formatDays(float $days): string
    {
        if (fmod($days, 1.0) === 0.0) {
            return ((int) $days).' ngày';
        }

        return number_format($days, 1, ',', '').' ngày';
    }
}
