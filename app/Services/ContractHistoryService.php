<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractTermination;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Services\ModuleChangeLogService;
use Carbon\Carbon;

class ContractHistoryService
{
    public const FIELD_LABELS = [
        'contract_code' => 'Mã HĐ',
        'start_date' => 'Ngày bắt đầu',
        'end_date' => 'Ngày kết thúc',
        'salary' => 'Lương cơ bản',
        'status' => 'Trạng thái',
        'contract_type_id' => 'Loại hợp đồng',
        'department_id' => 'Phòng ban',
        'position_id' => 'Chức vụ',
        'description' => 'Mô tả',
        'note' => 'Ghi chú',
        'allowance' => 'Phụ cấp',
        'allowance_meal' => 'Phụ cấp ăn',
        'allowance_phone' => 'Phụ cấp điện thoại',
        'allowance_fuel' => 'Phụ cấp xăng',
        'allowance_position' => 'Phụ cấp chức vụ',
        'signed_date' => 'Ngày ký',
    ];

    public const TRACKED_FIELDS = [
        'contract_code',
        'start_date',
        'end_date',
        'salary',
        'status',
        'contract_type_id',
        'department_id',
        'position_id',
        'description',
        'note',
        'allowance',
        'allowance_meal',
        'allowance_phone',
        'allowance_fuel',
        'allowance_position',
        'signed_date',
    ];

    public function logCreate(Contract $contract, ?int $performedBy = null): ContractHistory
    {
        $contract->loadMissing(['employee', 'contractType']);

        $summary = sprintf(
            '%s thêm hợp đồng %s (%s) cho nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $contract->contractType?->contract_name ?? '—',
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_CREATE, $summary, $performedBy);
    }

    public function logUpdate(Contract $contract, array $changes, ?int $performedBy = null): ?ContractHistory
    {
        if ($changes === []) {
            return null;
        }

        $formatted = $this->formatChanges($changes);

        $summary = sprintf(
            '%s sửa hợp đồng %s của nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store(
            $contract,
            ContractHistory::ACTION_UPDATE,
            $summary,
            $performedBy,
            ['changes' => $formatted],
        );
    }

    public function logExtend(Contract $oldContract, Contract $newContract, ?int $performedBy = null): ContractHistory
    {
        $oldContract->loadMissing('employee');

        $summary = sprintf(
            '%s gia hạn hợp đồng %s → %s cho nhân viên %s',
            $this->performerName($performedBy),
            $oldContract->contract_code,
            $newContract->contract_code,
            $this->employeeName($oldContract),
        );

        return $this->store(
            $oldContract,
            ContractHistory::ACTION_EXTEND,
            $summary,
            $performedBy,
            ['related_contract_id' => $newContract->id],
        );
    }

    public function logConvert(
        Contract $oldContract,
        Contract $newContract,
        string $sourceTypeName,
        string $targetTypeName,
        ?int $performedBy = null,
    ): ContractHistory {
        $oldContract->loadMissing('employee');

        $summary = sprintf(
            '%s chuyển loại hợp đồng %s → %s (%s → %s) cho nhân viên %s',
            $this->performerName($performedBy),
            $oldContract->contract_code,
            $newContract->contract_code,
            $sourceTypeName,
            $targetTypeName,
            $this->employeeName($oldContract),
        );

        return $this->store(
            $oldContract,
            ContractHistory::ACTION_CONVERT,
            $summary,
            $performedBy,
            ['related_contract_id' => $newContract->id],
        );
    }

    public function logCancel(Contract $contract, ?int $performedBy = null, ?string $note = null): ContractHistory
    {
        $summary = sprintf(
            '%s hủy hợp đồng %s của nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_CANCEL, $summary, $performedBy, ['note' => $note]);
    }

    public function logTerminate(Contract $contract, string $reason, ?int $performedBy = null, ?string $note = null): ContractHistory
    {
        $reasonLabel = ContractTermination::REASON_LABELS[$reason] ?? $reason;

        $summary = sprintf(
            '%s chấm dứt hợp đồng %s của nhân viên %s (lý do: %s)',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
            $reasonLabel,
        );

        return $this->store(
            $contract,
            ContractHistory::ACTION_TERMINATE,
            $summary,
            $performedBy,
            ['note' => $note],
        );
    }

    public function logActivate(Contract $contract, ?int $performedBy = null): ContractHistory
    {
        $summary = sprintf(
            '%s kích hoạt hợp đồng %s cho nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_ACTIVATE, $summary, $performedBy);
    }

    public function logDelete(Contract $contract, ?int $performedBy = null): ContractHistory
    {
        $summary = sprintf(
            '%s xóa mềm hợp đồng %s của nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_DELETE, $summary, $performedBy);
    }

    public function logRestore(Contract $contract, ?int $performedBy = null): ContractHistory
    {
        $summary = sprintf(
            '%s khôi phục hợp đồng %s của nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_RESTORE, $summary, $performedBy);
    }

    public function logForceDelete(Contract $contract, ?int $performedBy = null): ContractHistory
    {
        $summary = sprintf(
            '%s xóa vĩnh viễn hợp đồng %s của nhân viên %s',
            $this->performerName($performedBy),
            $contract->contract_code,
            $this->employeeName($contract),
        );

        return $this->store($contract, ContractHistory::ACTION_FORCE_DELETE, $summary, $performedBy);
    }

    public function collectChanges(Contract $contract, array $original): array
    {
        $contract->refresh();
        $changes = [];

        foreach (self::TRACKED_FIELDS as $field) {
            $oldValue = $original[$field] ?? null;
            $newValue = $contract->{$field};

            $normalizedOld = $this->normalizeValue($field, $oldValue);
            $normalizedNew = $this->normalizeValue($field, $newValue);

            if ($normalizedOld !== $normalizedNew) {
                $changes[$field] = [
                    'old' => $normalizedOld,
                    'new' => $normalizedNew,
                ];
            }
        }

        return $changes;
    }

    protected function store(
        Contract $contract,
        string $action,
        string $summary,
        ?int $performedBy,
        array $extra = [],
    ): ContractHistory {
        return ContractHistory::create([
            'employee_id' => $contract->employee_id,
            'contract_id' => $contract->id,
            'related_contract_id' => $extra['related_contract_id'] ?? null,
            'action' => $action,
            'summary' => $summary,
            'changes' => $extra['changes'] ?? null,
            'note' => $extra['note'] ?? null,
            'performed_by' => $performedBy,
        ])->tap(function (ContractHistory $history) {
            app(ModuleChangeLogService::class)->syncFromContractHistory($history);
        });
    }

    protected function performerName(?int $userId): string
    {
        if (! $userId) {
            return 'Hệ thống';
        }

        return User::query()->whereKey($userId)->value('name') ?? 'Hệ thống';
    }

    protected function employeeName(Contract $contract): string
    {
        $contract->loadMissing('employee');

        return $contract->employee?->full_name ?? '—';
    }

    protected function normalizeValue(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array($field, ['start_date', 'end_date', 'signed_date'], true)) {
            return Carbon::parse($value)->format('d/m/Y');
        }

        if ($field === 'status') {
            return Contract::STATUS_LABELS[$value] ?? (string) $value;
        }

        if ($field === 'contract_type_id') {
            return ContractType::query()->whereKey($value)->value('contract_name') ?? (string) $value;
        }

        if ($field === 'department_id') {
            return Department::query()->whereKey($value)->value('department_name') ?? (string) $value;
        }

        if ($field === 'position_id') {
            return Position::query()->whereKey($value)->value('position_name') ?? (string) $value;
        }

        if (in_array($field, ['salary', 'allowance', 'allowance_meal', 'allowance_phone', 'allowance_fuel', 'allowance_position'], true)) {
            return number_format((float) $value, 0, ',', '.') . '₫';
        }

        return (string) $value;
    }

    protected function formatChanges(array $changes): array
    {
        $formatted = [];

        foreach ($changes as $field => $pair) {
            $formatted[] = [
                'field' => $field,
                'label' => self::FIELD_LABELS[$field] ?? $field,
                'old' => $pair['old'] ?? null,
                'new' => $pair['new'] ?? null,
            ];
        }

        return $formatted;
    }
}
