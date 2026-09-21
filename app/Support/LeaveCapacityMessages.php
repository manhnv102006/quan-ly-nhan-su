<?php

namespace App\Support;

final class LeaveCapacityMessages
{
    /**
     * @param  list<array{day: \Carbon\Carbon, count: int}>  $fullDays
     */
    public static function employeeSubmitBlocked(
        string $departmentName,
        string $periodLabel,
        array $fullDays,
        int $maxSlots,
        int $percent,
        string $roleLabel,
        int $headcount,
    ): string {
        $daysText = self::formatDayList($fullDays);
        $peakCount = self::peakApprovedCount($fullDays);

        return implode("\n", [
            'Không thể gửi đơn nghỉ phép vì phòng ban «'.$departmentName.'» đã đạt giới hạn số người nghỉ trong khoảng '.$periodLabel.'.',
            '',
            '• Ngày đã đủ hạn mức: '.$daysText,
            '• Số người nghỉ (đơn đã duyệt): '.$peakCount.'/'.$maxSlots.' người/ngày',
            '• Hạn mức của bạn ('.$roleLabel.'): '.$percent.'% · Phòng ban: '.$headcount.' nhân viên đang làm việc',
            '',
            'Vui lòng chọn khoảng thời gian khác hoặc liên hệ quản lý. Đơn chờ duyệt không tính vào hạn mức; chỉ đơn đã duyệt mới được tính.',
        ]);
    }

    /**
     * @param  list<array{day: \Carbon\Carbon, count: int}>  $fullDays
     */
    public static function approvalBlocked(
        string $employeeName,
        string $departmentName,
        string $periodLabel,
        array $fullDays,
        int $maxSlots,
        int $percent,
        string $roleLabel,
        int $headcount,
    ): string {
        $daysText = self::formatDayList($fullDays);
        $peakCount = self::peakApprovedCount($fullDays);

        return implode("\n", [
            'Không thể phê duyệt đơn nghỉ phép của '.$employeeName.' (khoảng '.$periodLabel.').',
            '',
            '• Phòng ban: '.$departmentName.' ('.$headcount.' nhân viên đang làm việc)',
            '• Ngày đã đủ hạn mức: '.$daysText,
            '• Đã duyệt: '.$peakCount.'/'.$maxSlots.' người/ngày (hạn mức '.$percent.'% — '.$roleLabel.')',
            '',
            'Chỉ duyệt thêm được khi các ngày trên còn chỗ trống hoặc sau khi nhân viên kết thúc nghỉ và đi làm lại.',
        ]);
    }

    public static function noActiveStaff(bool $forApproval): string
    {
        return $forApproval
            ? 'Không thể phê duyệt: phòng ban chưa có nhân viên đang làm việc nên không xác định được hạn mức nghỉ phép.'
            : 'Không thể gửi đơn: phòng ban chưa có nhân viên đang làm việc nên không xác định được hạn mức nghỉ phép.';
    }

    /**
     * @param  list<array{day: \Carbon\Carbon, count: int, headcount?: int, max_slots?: int}>  $fullDays
     */
    public static function statutoryCapacityWarning(
        string $employeeName,
        string $departmentName,
        string $periodLabel,
        array $fullDays,
        int $maxSlots,
        int $percent,
        string $roleLabel,
        int $headcount,
    ): string {
        $daysText = self::formatDayList($fullDays);
        $peakCount = self::peakApprovedCount($fullDays);

        return implode("\n", [
            'Cảnh báo: phòng ban «'.$departmentName.'» đã đạt hoặc vượt giới hạn nghỉ trong khoảng '.$periodLabel.'.',
            '',
            '• Loại nghỉ «'.self::statutoryLeaveLabel().'» không bị chặn bởi giới hạn phòng ban.',
            '• Ngày đã đủ hạn mức: '.$daysText,
            '• Số người nghỉ (đơn đã duyệt, không tính loại theo luật): '.$peakCount.'/'.$maxSlots.' người/ngày',
            '• Hạn mức tham chiếu ('.$roleLabel.'): '.$percent.'% · Nhân sự đang làm việc: '.$headcount.' người',
            '',
            'Bạn vẫn có thể duyệt đơn của '.$employeeName.' nhưng nên cân nhắc nguồn lực phòng ban.',
        ]);
    }

    public static function bulkApprovePartialFailure(int $failedCount): string
    {
        return $failedCount.' đơn không được duyệt do đã đạt giới hạn nghỉ phép phòng ban ('
            .LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_MANAGER_ACCOUNTANT).'% đối với quản lý/kế toán, '
            .LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE).'% đối với nhân viên — tính theo từng ngày làm việc trong khoảng nghỉ).';
    }

    /**
     * @param  list<array{day: \Carbon\Carbon, count: int}>  $fullDays
     */
    private static function formatDayList(array $fullDays): string
    {
        $labels = collect($fullDays)->map(fn (array $row) => $row['day']->format('d/m/Y'))->values();

        if ($labels->count() <= 4) {
            return $labels->join(', ');
        }

        return $labels->take(3)->join(', ').' và '.($labels->count() - 3).' ngày khác';
    }

    /**
     * @param  list<array{day: \Carbon\Carbon, count: int}>  $fullDays
     */
    private static function peakApprovedCount(array $fullDays): int
    {
        if ($fullDays === []) {
            return 0;
        }

        return (int) max(array_column($fullDays, 'count'));
    }

    private static function statutoryLeaveLabel(): string
    {
        return 'thai sản, ốm, hiếu, kết hôn';
    }
}
