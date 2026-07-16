<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\OvertimeRequest;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoNotificationService
{
    public function __construct(
        private AdminNotificationService $notifications,
    ) {}

    public function leaveSubmitted(LeaveRequest $leaveRequest): void
    {
        $this->afterCommit(function () use ($leaveRequest) {
            $leaveRequest->loadMissing('employee.department');
            $employee = $leaveRequest->employee;

            if (! $employee) {
                return;
            }

            $recipients = $this->resolveRecipients(
                $this->adminUserIds(),
                [$this->departmentManagerUserId($employee->department_id)],
            );

            $recipients = $this->excludeUserIds($recipients, [$employee->user_id]);

            $this->send('leave', 'Đơn nghỉ phép mới', sprintf(
                '%s gửi đơn %s từ %s đến %s (%s ngày), đang chờ duyệt.',
                $employee->full_name,
                $this->leaveTypeLabel($leaveRequest->leave_type),
                $leaveRequest->start_date->format('d/m/Y'),
                $leaveRequest->end_date->format('d/m/Y'),
                $leaveRequest->total_days,
            ), $recipients, $employee->department_id);
        });
    }

    public function leaveApproved(LeaveRequest $leaveRequest): void
    {
        $this->afterCommit(function () use ($leaveRequest) {
            $leaveRequest->loadMissing('employee');
            $userId = $leaveRequest->employee?->user_id;

            if (! $userId) {
                return;
            }

            $this->send('leave', 'Đơn nghỉ phép đã được duyệt', sprintf(
                'Đơn %s từ %s đến %s của bạn đã được phê duyệt.',
                $this->leaveTypeLabel($leaveRequest->leave_type),
                $leaveRequest->start_date->format('d/m/Y'),
                $leaveRequest->end_date->format('d/m/Y'),
            ), [$userId], $leaveRequest->employee?->department_id);
        });
    }

    public function leaveRejected(LeaveRequest $leaveRequest): void
    {
        $this->afterCommit(function () use ($leaveRequest) {
            $leaveRequest->loadMissing('employee');
            $userId = $leaveRequest->employee?->user_id;

            if (! $userId) {
                return;
            }

            $reason = $leaveRequest->reject_reason
                ? ' Lý do: '.$leaveRequest->reject_reason
                : '';

            $this->send('leave', 'Đơn nghỉ phép bị từ chối', sprintf(
                'Đơn %s từ %s đến %s của bạn đã bị từ chối.%s',
                $this->leaveTypeLabel($leaveRequest->leave_type),
                $leaveRequest->start_date->format('d/m/Y'),
                $leaveRequest->end_date->format('d/m/Y'),
                $reason,
            ), [$userId], $leaveRequest->employee?->department_id);
        });
    }

    public function payrollApproved(PayrollPeriod $period): void
    {
        $this->afterCommit(function () use ($period) {
            $recipients = $this->payrollPeriodEmployeeUserIds($period);

            $this->send('payroll', 'Bảng lương đã được duyệt', sprintf(
                'Kỳ lương %s (tháng %d/%d) đã được phê duyệt.',
                $period->name,
                $period->month,
                $period->year,
            ), $recipients);
        });
    }

    public function payrollPaid(PayrollPeriod $period): void
    {
        $this->afterCommit(function () use ($period) {
            $recipients = $this->payrollPeriodEmployeeUserIds($period);

            $this->send('payroll', 'Lương đã được chi trả', sprintf(
                'Kỳ lương %s (tháng %d/%d) đã hoàn tất chi trả. Vui lòng kiểm tra bảng lương.',
                $period->name,
                $period->month,
                $period->year,
            ), $recipients);
        });
    }

    public function employeeInsufficientWorkDaysWarning(Employee $employee, PayrollPeriod $period, int $actualDays, int $unpaidLeaves): void
    {
        $this->afterCommit(function () use ($employee, $period, $actualDays, $unpaidLeaves) {
            // Gửi báo cáo cho Manager và Admin
            if (! $employee->department_id) {
                return;
            }

            $recipients = $this->resolveRecipients(
                $this->adminUserIds(),
                [$this->departmentManagerUserId($employee->department_id)],
            );

            // Bỏ qua nếu chính nhân viên đó là admin/manager để họ không nhận tin nhắn "Mách lẻo" về chính mình nữa
            $recipients = $this->excludeUserIds($recipients, [$employee->user_id]);

            if (! empty($recipients)) {
                $this->send('system', 'Báo cáo: Nhân viên nghỉ không phép quá nhiều', sprintf(
                    'Nhân viên %s chỉ đạt %d công trong kỳ %s (có %d ngày nghỉ không phép). Vui lòng nhắc nhở nhân viên vì không đáp ứng đủ quy định 23 công/tháng.',
                    $employee->full_name,
                    $actualDays,
                    $period->name,
                    $unpaidLeaves,
                ), $recipients, $employee->department_id);
            }
        });
    }

    public function kpiAssigned(KPIAssignment $assignment): void
    {
        $this->afterCommit(function () use ($assignment) {
            $assignment->loadMissing('kpi');

            $this->send('kpi', 'KPI mới được giao', sprintf(
                'Bạn được giao theo dõi KPI "%s" (%s). Vui lòng xác nhận và cập nhật tiến độ.',
                $assignment->kpi?->title ?? 'KPI',
                $assignment->kpi?->code ?? '',
            ), [$assignment->manager_id], $assignment->kpi?->department_id);
        });
    }

    public function kpiApproved(KPIAssignment $assignment): void
    {
        $this->afterCommit(function () use ($assignment) {
            $assignment->loadMissing('kpi');

            $this->send('kpi', 'Giao KPI đã được kích hoạt', sprintf(
                'Giao KPI "%s" cho quản lý đã được phê duyệt và đang thực hiện.',
                $assignment->kpi?->title ?? 'KPI',
            ), array_filter([$assignment->assigned_by]), $assignment->kpi?->department_id);
        });
    }

    public function kpiRejected(KPIAssignment $assignment): void
    {
        $this->afterCommit(function () use ($assignment) {
            $assignment->loadMissing('kpi');

            $this->send('kpi', 'Giao KPI đã bị hủy', sprintf(
                'Giao KPI "%s" đã bị hủy.',
                $assignment->kpi?->title ?? 'KPI',
            ), array_filter([$assignment->assigned_by, $assignment->manager_id]), $assignment->kpi?->department_id);
        });
    }

    public function kpiCompleted(KPIAssignment $assignment): void
    {
        $this->afterCommit(function () use ($assignment) {
            $assignment->loadMissing('kpi');

            $this->send('kpi', 'KPI đã hoàn thành', sprintf(
                'Giao KPI "%s" đã được đánh dấu hoàn thành.',
                $assignment->kpi?->title ?? 'KPI',
            ), array_filter([$assignment->assigned_by]), $assignment->kpi?->department_id);
        });
    }

    public function overtimeApproved(OvertimeRequest $request): void
    {
        $this->afterCommit(function () use ($request) {
            $request->loadMissing('employee');
            $userId = $request->employee?->user_id;

            if (! $userId) {
                return;
            }

            $this->send('system', 'Đơn tăng ca đã được duyệt', sprintf(
                'Đơn tăng ca ngày %s của bạn đã được phê duyệt.',
                $request->work_date?->format('d/m/Y'),
            ), [$userId], $request->employee?->department_id);
        });
    }

    public function overtimeRejected(OvertimeRequest $request): void
    {
        $this->afterCommit(function () use ($request) {
            $request->loadMissing('employee');
            $userId = $request->employee?->user_id;

            if (! $userId) {
                return;
            }

            $this->send('system', 'Đơn tăng ca bị từ chối', sprintf(
                'Đơn tăng ca ngày %s của bạn đã bị từ chối.',
                $request->work_date?->format('d/m/Y'),
            ), [$userId], $request->employee?->department_id);
        });
    }

    public function notifyExpiringContracts(array $reminderDays = [30, 15, 7, 1]): int
    {
        $sent = 0;
        $placeholders = implode(',', array_fill(0, count($reminderDays), '?'));

        Contract::query()
            ->select('contracts.*')
            ->selectRaw('DATEDIFF(end_date, CURDATE()) as days_left')
            ->with(['employee.department'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereRaw("DATEDIFF(end_date, CURDATE()) IN ({$placeholders})", $reminderDays)
            ->each(function (Contract $contract) use (&$sent) {
                $employee = $contract->employee;

                if (! $employee) {
                    return;
                }

                $daysLeft = (int) $contract->days_left;

                if ($daysLeft < 0 || $this->contractReminderAlreadySent($contract, $daysLeft)) {
                    return;
                }

                $recipients = $this->resolveRecipients(
                    $this->adminUserIds(),
                    array_filter([
                        $employee->user_id,
                        $this->departmentManagerUserId($employee->department_id),
                    ]),
                );

                $created = $this->send('system', 'Hợp đồng sắp hết hạn', sprintf(
                    'Hợp đồng %s của %s sẽ hết hạn vào %s (còn %d ngày).',
                    $contract->contract_code,
                    $employee->full_name,
                    $contract->end_date->format('d/m/Y'),
                    $daysLeft,
                ), $recipients, $employee->department_id);

                if ($created) {
                    $sent++;
                }
            });

        return $sent;
    }

    public function notifyContractRenewed(Contract $oldContract, Contract $newContract, ?int $performerId = null): bool
    {
        $employee = $oldContract->employee;
        if (! $employee) {
            return false;
        }

        $performerName = $performerId
            ? (User::query()->whereKey($performerId)->value('name') ?? 'Quản trị viên')
            : 'Quản trị viên';

        $recipients = $this->resolveRecipients(
            array_filter([
                $employee->user_id,
                $this->departmentManagerUserId($employee->department_id),
            ]),
        );

        return $this->send(
            'system',
            'Hợp đồng đã được gia hạn',
            sprintf(
                'Hợp đồng %s của %s đã được gia hạn thành %s (hiệu lực từ %s). Người thực hiện: %s.',
                $oldContract->contract_code,
                $employee->full_name,
                $newContract->contract_code,
                $newContract->start_date?->format('d/m/Y') ?? '—',
                $performerName,
            ),
            $recipients,
            $employee->department_id,
        );
    }

    public function clearContractExpiringNotifications(Contract $contract): int
    {
        $notifications = Notification::query()
            ->where('type', 'system')
            ->where('title', 'Hợp đồng sắp hết hạn')
            ->where('content', 'like', '%'.$contract->contract_code.'%')
            ->get();

        $removed = 0;

        foreach ($notifications as $notification) {
            $notification->users()->detach();
            $notification->delete();
            $removed++;
        }

        return $removed;
    }

    public function notifyContractConverted(Contract $oldContract, Contract $newContract, ?int $performerId = null): bool
    {
        $employee = $oldContract->employee;
        if (! $employee) {
            return false;
        }

        $oldContract->loadMissing('contractType');
        $newContract->loadMissing('contractType');

        $performerName = $performerId
            ? (User::query()->whereKey($performerId)->value('name') ?? 'Quản trị viên')
            : 'Quản trị viên';

        $recipients = $this->resolveRecipients(
            array_filter([
                $employee->user_id,
                $this->departmentManagerUserId($employee->department_id),
            ]),
        );

        return $this->send(
            'system',
            'Hợp đồng đã chuyển loại',
            sprintf(
                'Hợp đồng %s của %s đã chuyển từ "%s" sang "%s" (HĐ mới: %s). Người thực hiện: %s.',
                $oldContract->contract_code,
                $employee->full_name,
                $oldContract->contractType?->contract_name ?? '—',
                $newContract->contractType?->contract_name ?? '—',
                $newContract->contract_code,
                $performerName,
            ),
            $recipients,
            $employee->department_id,
        );
    }

    private function send(string $type, string $title, string $content, array $userIds, ?int $departmentId = null): bool
    {
        $userIds = $this->resolveRecipients($userIds);

        if ($userIds === []) {
            Log::info('Auto notification skipped: no active recipients.', compact('type', 'title'));

            return false;
        }

        try {
            return $this->notifications->createSystem([
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'department_id' => $departmentId,
            ], $userIds) !== null;
        } catch (\Throwable $exception) {
            Log::error('Auto notification failed.', [
                'type' => $type,
                'title' => $title,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function afterCommit(callable $callback): void
    {
        DB::afterCommit($callback);
    }

    private function resolveRecipients(array ...$groups): array
    {
        $userIds = [];

        foreach ($groups as $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ($group as $userId) {
                if ($userId) {
                    $userIds[] = (int) $userId;
                }
            }
        }

        return $this->notifications->filterActiveUserIds(array_values(array_unique($userIds)));
    }

    private function excludeUserIds(array $userIds, array $excludeIds): array
    {
        $exclude = array_filter(array_map('intval', $excludeIds));

        if ($exclude === []) {
            return $userIds;
        }

        return array_values(array_diff($userIds, $exclude));
    }

    private function adminUserIds(): array
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->where('name', Role::ADMIN))
            ->pluck('id')
            ->all();
    }

    private function departmentManagerUserId(?int $departmentId): ?int
    {
        if (! $departmentId) {
            return null;
        }

        $department = Department::find($departmentId);

        if (! $department?->manager_id) {
            return null;
        }

        return Employee::query()
            ->whereKey($department->manager_id)
            ->value('user_id');
    }

    private function payrollPeriodEmployeeUserIds(PayrollPeriod $period): array
    {
        $userIds = $period->payrolls()
            ->join('employees', 'employees.id', '=', 'payrolls.employee_id')
            ->whereNotNull('employees.user_id')
            ->pluck('employees.user_id')
            ->all();

        return $this->resolveRecipients($userIds);
    }

    private function contractReminderAlreadySent(Contract $contract, int $daysLeft): bool
    {
        return Notification::query()
            ->where('type', 'system')
            ->where('title', 'Hợp đồng sắp hết hạn')
            ->where('content', 'like', '%'.$contract->contract_code.'%')
            ->where('content', 'like', '%(còn '.$daysLeft.' ngày)%')
            ->whereDate('created_at', today())
            ->exists();
    }

    private function leaveTypeLabel(string $type): string
    {
        return match ($type) {
            'annual' => 'nghỉ phép năm',
            'sick' => 'nghỉ ốm',
            'unpaid' => 'nghỉ không lương',
            default => 'nghỉ phép',
        };
    }
}
