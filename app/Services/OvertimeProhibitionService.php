<?php

namespace App\Services;

use App\Models\Employee;

/**
 * Cấm tăng ca theo Điều 137 BLLĐ — lao động nữ mang thai từ tháng thứ 7 hoặc nuôi con dưới 12 tháng.
 */
class OvertimeProhibitionService
{
    public function violationMessage(int $employeeId): ?string
    {
        $employee = Employee::query()->find($employeeId);

        if (! $employee || ! $employee->isOvertimeProhibited()) {
            return null;
        }

        return match ($employee->overtime_ban_status) {
            Employee::OT_BAN_PREGNANCY_7M => 'Không được làm thêm giờ: nhân viên đang mang thai từ tháng thứ 7 (Điều 137 BLLĐ).',
            Employee::OT_BAN_NURSING_UNDER_12M => 'Không được làm thêm giờ: nhân viên đang nuôi con dưới 12 tháng tuổi (Điều 137 BLLĐ).',
            default => 'Không được làm thêm giờ theo quy định bảo vệ lao động nữ (Điều 137 BLLĐ).',
        };
    }
}
