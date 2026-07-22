<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use App\Support\TimeInput;
use Carbon\Carbon;

/**
 * Kiểm tra giới hạn giờ làm thêm theo Bộ luật Lao động Việt Nam 2019.
 *
 * - Ngày: không quá 50% số giờ làm việc bình thường (8h) => tối đa 4h/ngày;
 *   tổng giờ làm bình thường + tăng ca không quá 12h/ngày.
 * - Tháng: không quá 40 giờ.
 * - Năm: không quá 200 giờ (một số ngành đặc thù tối đa 300 giờ).
 */
class OvertimeLimitService
{
    public const STANDARD_WORK_HOURS_PER_DAY = 8.0;

    public const MAX_HOURS_PER_DAY = 4.0; // 50% của 8 giờ làm việc bình thường

    public const MAX_HOURS_PER_MONTH = 40.0;

    public const MAX_HOURS_PER_YEAR = 200.0;

    /**
     * Số giờ giữa hai mốc thời gian HH:MM (hỗ trợ ca qua đêm).
     */
    public function hoursBetween(string $startTime, string $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', TimeInput::forInput($startTime));
        $end = Carbon::createFromFormat('H:i', TimeInput::forInput($endTime));

        $minutes = $start->diffInMinutes($end, false);
        if ($minutes < 0) {
            $minutes += 24 * 60;
        }

        return round($minutes / 60, 2);
    }

    /**
     * Trả về danh sách lỗi vi phạm giới hạn (rỗng nếu hợp lệ).
     *
     * @return array<string, string> map field => message
     */
    public function violations(int $employeeId, string $workDate, float $newHours, ?int $ignoreId = null): array
    {
        $date = Carbon::parse($workDate);
        $errors = [];

        // Giới hạn theo ngày
        $dayExisting = $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereDate('work_date', $date->format('Y-m-d'));
        });
        $dayTotal = round($dayExisting + $newHours, 2);

        if ($dayTotal > self::MAX_HOURS_PER_DAY) {
            $errors['start_time'] = sprintf(
                'Vượt giới hạn tăng ca trong ngày: tối đa %sh/ngày (50%% giờ làm bình thường). Đã có %sh, đơn này %sh → tổng %sh.',
                $this->format(self::MAX_HOURS_PER_DAY),
                $this->format($dayExisting),
                $this->format($newHours),
                $this->format($dayTotal),
            );
        }

        // Giới hạn theo tháng
        $monthExisting = $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereYear('work_date', $date->year)
                ->whereMonth('work_date', $date->month);
        });
        $monthTotal = round($monthExisting + $newHours, 2);

        if ($monthTotal > self::MAX_HOURS_PER_MONTH) {
            $errors['work_date'] = sprintf(
                'Vượt giới hạn tăng ca trong tháng %02d/%d: tối đa %sh/tháng. Đã có %sh, đơn này %sh → tổng %sh.',
                $date->month,
                $date->year,
                $this->format(self::MAX_HOURS_PER_MONTH),
                $this->format($monthExisting),
                $this->format($newHours),
                $this->format($monthTotal),
            );
        }

        // Giới hạn theo năm
        $yearExisting = $this->sumHours($employeeId, $ignoreId, function ($query) use ($date) {
            $query->whereYear('work_date', $date->year);
        });
        $yearTotal = round($yearExisting + $newHours, 2);

        if ($yearTotal > self::MAX_HOURS_PER_YEAR) {
            $errors['work_date'] = sprintf(
                'Vượt giới hạn tăng ca trong năm %d: tối đa %sh/năm. Đã có %sh, đơn này %sh → tổng %sh.',
                $date->year,
                $this->format(self::MAX_HOURS_PER_YEAR),
                $this->format($yearExisting),
                $this->format($newHours),
                $this->format($yearTotal),
            );
        }

        return $errors;
    }

    /**
     * Tổng giờ tăng ca đã ghi nhận (pending + approved + completed) theo điều kiện lọc.
     */
    private function sumHours(int $employeeId, ?int $ignoreId, callable $scope): float
    {
        $query = OvertimeRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [
                OvertimeRequest::STATUS_PENDING,
                OvertimeRequest::STATUS_APPROVED,
                OvertimeRequest::STATUS_COMPLETED,
            ])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));

        $scope($query);

        return (float) $query->sum('total_hours');
    }

    private function format(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }
}
