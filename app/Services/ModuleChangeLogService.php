<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\EmployeeInsurance;
use App\Models\ModuleChangeLog;
use App\Models\Position;
use App\Models\SalaryAdvance;
use App\Models\TaxDependent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ModuleChangeLogService
{
    public const INSURANCE_FIELDS = [
        'social_insurance_number' => 'Số BHXH',
        'health_insurance_code' => 'Mã BHYT',
        'contribution_salary' => 'Mức lương đóng BH',
        'bhxh_employee_rate' => 'Tỷ lệ BHXH NLĐ',
        'bhxh_employer_rate' => 'Tỷ lệ BHXH DN',
        'bhyt_employee_rate' => 'Tỷ lệ BHYT NLĐ',
        'bhyt_employer_rate' => 'Tỷ lệ BHYT DN',
        'bhtn_employee_rate' => 'Tỷ lệ BHTN NLĐ',
        'bhtn_employer_rate' => 'Tỷ lệ BHTN DN',
        'start_date' => 'Ngày bắt đầu đóng BH',
        'end_date' => 'Ngày kết thúc đóng BH',
        'status' => 'Trạng thái BH',
        'stop_reason' => 'Lý do ngừng đóng BH',
        'note' => 'Ghi chú BH',
    ];

    public const TAX_PROFILE_FIELDS = [
        'tax_code' => 'Mã số thuế',
        'personal_deduction' => 'Giảm trừ bản thân',
        'note' => 'Ghi chú hồ sơ thuế',
    ];

    public const TAX_DEPENDENT_FIELDS = [
        'full_name' => 'Họ tên NPT',
        'relationship' => 'Quan hệ NPT',
        'date_of_birth' => 'Ngày sinh NPT',
        'id_number' => 'CCCD/CMND NPT',
        'monthly_deduction' => 'Giảm trừ phụ thuộc/tháng',
        'start_date' => 'Ngày bắt đầu NPT',
        'end_date' => 'Ngày kết thúc NPT',
        'is_active' => 'Trạng thái NPT',
        'note' => 'Ghi chú NPT',
    ];

    public const PAYROLL_FIELDS = [
        'bonus' => 'Tiền thưởng/KPI',
        'deduction' => 'Tiền khấu trừ/phạt',
        'total_salary' => 'Thực lĩnh',
        'status' => 'Trạng thái bảng lương',
    ];

    public const ADVANCE_FIELDS = [
        'amount' => 'Số tiền tạm ứng',
        'request_date' => 'Ngày yêu cầu',
        'reason' => 'Lý do tạm ứng',
        'note' => 'Ghi chú tạm ứng',
        'status' => 'Trạng thái tạm ứng',
        'amount_settled' => 'Số tiền đã trừ',
        'rejection_reason' => 'Lý do từ chối',
    ];

    public function logFieldChange(
        string $module,
        string $action,
        Model $entity,
        ?int $employeeId,
        string $fieldName,
        string $fieldLabel,
        mixed $oldValue,
        mixed $newValue,
        ?string $note = null,
        ?int $userId = null,
    ): ModuleChangeLog {
        [$userName, $userRole] = $this->resolveUser($userId);

        return ModuleChangeLog::create([
            'module' => $module,
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'employee_id' => $employeeId,
            'field_name' => $fieldName,
            'field_label' => $fieldLabel,
            'old_value' => $this->stringify($oldValue),
            'new_value' => $this->stringify($newValue),
            'note' => $note,
            'user_id' => $userId ?? auth()->id(),
            'user_name' => $userName,
            'user_role' => $userRole,
        ]);
    }

    public function logAction(
        string $module,
        string $action,
        Model $entity,
        ?int $employeeId,
        string $fieldLabel,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?string $note = null,
        ?int $userId = null,
    ): ModuleChangeLog {
        return $this->logFieldChange(
            $module,
            $action,
            $entity,
            $employeeId,
            'action',
            $fieldLabel,
            $oldValue,
            $newValue,
            $note,
            $userId,
        );
    }

    /**
     * @param  array<string, mixed>  $original
     * @param  array<string, string>  $fieldLabels
     */
    public function logModelChanges(
        string $module,
        string $action,
        Model $entity,
        ?int $employeeId,
        array $original,
        array $fieldLabels,
        ?array $onlyFields = null,
        ?string $note = null,
        ?int $userId = null,
        ?callable $normalizer = null,
    ): void {
        $entity->refresh();

        foreach ($fieldLabels as $field => $label) {
            if ($onlyFields !== null && !in_array($field, $onlyFields, true)) {
                continue;
            }

            $oldRaw = $original[$field] ?? null;
            $newRaw = $entity->{$field};

            $old = $normalizer ? $normalizer($field, $oldRaw) : $this->normalizeGeneric($field, $oldRaw);
            $new = $normalizer ? $normalizer($field, $newRaw) : $this->normalizeGeneric($field, $newRaw);

            if ($old === $new) {
                continue;
            }

            $this->logFieldChange(
                $module,
                $action,
                $entity,
                $employeeId,
                $field,
                $label,
                $old,
                $new,
                $note,
                $userId,
            );
        }
    }

    public function logInsuranceCreate(EmployeeInsurance $profile, ?int $userId = null): void
    {
        $this->logAction(
            ModuleChangeLog::MODULE_INSURANCE,
            'create',
            $profile,
            $profile->employee_id,
            'Hồ sơ bảo hiểm',
            null,
            $this->formatMoney($profile->contribution_salary),
            'Tạo hồ sơ BH mới',
            $userId,
        );
    }

    public function logInsuranceUpdate(EmployeeInsurance $profile, array $original, ?int $userId = null): void
    {
        $this->logModelChanges(
            ModuleChangeLog::MODULE_INSURANCE,
            'update',
            $profile,
            $profile->employee_id,
            $original,
            self::INSURANCE_FIELDS,
            null,
            null,
            $userId,
            fn($field, $value) => $this->normalizeInsurance($field, $value),
        );
    }

    public function logInsuranceStop(EmployeeInsurance $profile, array $original, ?int $userId = null): void
    {
        $this->logModelChanges(
            ModuleChangeLog::MODULE_INSURANCE,
            'stop',
            $profile,
            $profile->employee_id,
            $original,
            self::INSURANCE_FIELDS,
            ['status', 'end_date', 'stop_reason'],
            $profile->stop_reason,
            $userId,
            fn($field, $value) => $this->normalizeInsurance($field, $value),
        );
    }

    public function logTaxProfileUpdate(Model $profile, array $original, int $employeeId, ?int $userId = null): void
    {
        $this->logModelChanges(
            ModuleChangeLog::MODULE_TAX,
            'update',
            $profile,
            $employeeId,
            $original,
            self::TAX_PROFILE_FIELDS,
            null,
            null,
            $userId,
            fn($field, $value) => $this->normalizeTax($field, $value),
        );
    }

    public function logTaxDependentCreate(TaxDependent $dependent, ?int $userId = null): void
    {
        $this->logAction(
            ModuleChangeLog::MODULE_TAX,
            'create',
            $dependent,
            $dependent->employee_id,
            'Người phụ thuộc',
            null,
            $dependent->full_name,
            null,
            $userId,
        );
    }

    public function logTaxDependentUpdate(TaxDependent $dependent, array $original, ?int $userId = null): void
    {
        $this->logModelChanges(
            ModuleChangeLog::MODULE_TAX,
            'update',
            $dependent,
            $dependent->employee_id,
            $original,
            self::TAX_DEPENDENT_FIELDS,
            null,
            null,
            $userId,
            fn($field, $value) => $this->normalizeTax($field, $value),
        );
    }

    public function logTaxDependentDelete(TaxDependent $dependent, ?int $userId = null): void
    {
        $this->logAction(
            ModuleChangeLog::MODULE_TAX,
            'delete',
            $dependent,
            $dependent->employee_id,
            'Người phụ thuộc',
            $dependent->full_name,
            null,
            null,
            $userId,
        );
    }

    public function logPayrollAdjust(
        Model $payroll,
        int $employeeId,
        float $oldBonus,
        float $oldDeduction,
        float $newBonus,
        float $newDeduction,
        string $reason,
        ?int $userId = null,
    ): void {
        if ($oldBonus != $newBonus) {
            $this->logFieldChange(
                ModuleChangeLog::MODULE_PAYROLL,
                'adjust',
                $payroll,
                $employeeId,
                'bonus',
                self::PAYROLL_FIELDS['bonus'],
                $this->formatMoney($oldBonus),
                $this->formatMoney($newBonus),
                $reason,
                $userId,
            );
        }

        if ($oldDeduction != $newDeduction) {
            $this->logFieldChange(
                ModuleChangeLog::MODULE_PAYROLL,
                'adjust',
                $payroll,
                $employeeId,
                'deduction',
                self::PAYROLL_FIELDS['deduction'],
                $this->formatMoney($oldDeduction),
                $this->formatMoney($newDeduction),
                $reason,
                $userId,
            );
        }
    }

    public function logAdvanceCreate(SalaryAdvance $advance, ?int $userId = null): void
    {
        $this->logAction(
            ModuleChangeLog::MODULE_ADVANCE,
            'create',
            $advance,
            $advance->employee_id,
            'Yêu cầu tạm ứng',
            null,
            $advance->advance_code . ' · ' . $this->formatMoney($advance->amount),
            $advance->reason,
            $userId,
        );
    }

    public function logAdvanceStatusChange(
        SalaryAdvance $advance,
        string $action,
        string $oldStatus,
        string $newStatus,
        ?string $note = null,
        ?int $userId = null,
    ): void {
        $this->logFieldChange(
            ModuleChangeLog::MODULE_ADVANCE,
            $action,
            $advance,
            $advance->employee_id,
            'status',
            self::ADVANCE_FIELDS['status'],
            SalaryAdvance::STATUS_LABELS[$oldStatus] ?? $oldStatus,
            SalaryAdvance::STATUS_LABELS[$newStatus] ?? $newStatus,
            $note,
            $userId,
        );
    }

    public function logAdvanceDeduction(
        SalaryAdvance $advance,
        float $deductedAmount,
        float $remainingBefore,
        ?string $note = null,
        ?int $userId = null,
    ): void {
        $this->logFieldChange(
            ModuleChangeLog::MODULE_ADVANCE,
            'deduct',
            $advance,
            $advance->employee_id,
            'amount_settled',
            'Trừ tạm ứng vào lương',
            $this->formatMoney($remainingBefore),
            $this->formatMoney($advance->remainingBalance()),
            'Trừ ' . $this->formatMoney($deductedAmount) . ($note ? ' · ' . $note : ''),
            $userId,
        );
    }

    public function syncFromContractHistory(ContractHistory $history): void
    {
        $contract = $history->contract;
        if (!$contract) {
            return;
        }

        $userId = $history->performed_by;
        $action = $history->action === ContractHistory::ACTION_UPDATE ? 'update' : $history->action;

        if ($history->action === ContractHistory::ACTION_UPDATE && is_array($history->changes) && $history->changes !== []) {
            foreach ($history->changes as $change) {
                $this->logFieldChange(
                    ModuleChangeLog::MODULE_CONTRACT,
                    'update',
                    $contract,
                    $history->employee_id,
                    $change['field'] ?? 'unknown',
                    $change['label'] ?? ($change['field'] ?? 'Trường'),
                    $change['old'] ?? null,
                    $change['new'] ?? null,
                    $history->note,
                    $userId,
                );
            }

            return;
        }

        $this->logAction(
            ModuleChangeLog::MODULE_CONTRACT,
            $action,
            $contract,
            $history->employee_id,
            ContractHistory::ACTION_LABELS[$history->action] ?? $history->action,
            null,
            $history->summary,
            $history->note,
            $userId,
        );
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    protected function resolveUser(?int $userId): array
    {
        $user = $userId
            ? User::with('role')->find($userId)
            : auth()->user()?->loadMissing('role');

        if (!$user) {
            return ['Hệ thống', null];
        }

        return [$user->name, $user->role?->name];
    }

    protected function stringify(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    protected function normalizeGeneric(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        if (in_array($field, ['start_date', 'end_date', 'request_date', 'date_of_birth', 'signed_date'], true)) {
            return Carbon::parse($value)->format('d/m/Y');
        }

        if (in_array($field, ['salary', 'amount', 'bonus', 'deduction', 'total_salary', 'contribution_salary', 'personal_deduction', 'monthly_deduction', 'amount_settled'], true)) {
            return $this->formatMoney($value);
        }

        return (string) $value;
    }

    protected function normalizeInsurance(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (str_contains($field, '_rate')) {
            return round((float) $value * 100, 2) . '%';
        }

        if ($field === 'status') {
            return match ($value) {
                EmployeeInsurance::STATUS_ACTIVE => 'Đang đóng',
                EmployeeInsurance::STATUS_SUSPENDED => 'Tạm dừng',
                EmployeeInsurance::STATUS_STOPPED => 'Đã ngừng',
                default => (string) $value,
            };
        }

        if (in_array($field, ['start_date', 'end_date'], true)) {
            return Carbon::parse($value)->format('d/m/Y');
        }

        if ($field === 'contribution_salary') {
            return $this->formatMoney($value);
        }

        return (string) $value;
    }

    protected function normalizeTax(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($field === 'relationship') {
            return TaxDependent::RELATIONSHIP_LABELS[$value] ?? (string) $value;
        }

        if ($field === 'is_active') {
            return $value ? 'Đang áp dụng' : 'Ngừng áp dụng';
        }

        if (in_array($field, ['start_date', 'end_date', 'date_of_birth'], true)) {
            return Carbon::parse($value)->format('d/m/Y');
        }

        if (in_array($field, ['personal_deduction', 'monthly_deduction'], true)) {
            return $this->formatMoney($value);
        }

        return (string) $value;
    }

    protected function formatMoney(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.') . '₫';
    }
}
