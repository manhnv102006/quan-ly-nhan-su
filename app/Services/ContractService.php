<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractExtension;
use App\Models\ContractTermination;
use App\Models\ContractType;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Rules\NoContractOverlap;

class ContractService
{
    /**
     * Phụ cấp cố định cho mọi hợp đồng: 1.500.000đ.
     */
    public const FIXED_ALLOWANCE = 1_500_000;

    public function __construct(
        private readonly ContractAllowanceService $allowanceService,
        private readonly ContractTypeValidationService $typeValidation,
        private readonly ContractTypeConversionService $conversionValidation,
        private readonly AutoNotificationService $autoNotifications,
        private readonly ContractHistoryService $historyService,
    ) {
    }

    /**
     * Phụ cấp áp dụng theo loại hợp đồng: HĐ thực tập không có phụ cấp.
     */
    public function allowanceForContractType($contractTypeId): int
    {
        $type = $contractTypeId ? ContractType::find($contractTypeId) : null;

        return $type && $type->isInternship() ? 0 : self::FIXED_ALLOWANCE;
    }

    /**
     * Sinh mã hợp đồng theo định dạng HD-YYYY-0001
     */
    public function generateCode(): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = "HD-{$year}-";
        $last = Contract::withTrashed()
            ->where('contract_code', 'like', $prefix . '%')
            ->orderByDesc('contract_code')
            ->value('contract_code');

        $nextNumber = 1;
        if ($last && preg_match('/^HD-\d{4}-(\d{4})$/', $last, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tạo hợp đồng mới (hợp đồng đầu tiên của nhân viên hoặc sau khi không còn HĐ active).
     */
    public function create(array $data, ?int $creatorId = null): Contract
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $employee = Employee::findOrFail($data['employee_id']);
            $this->assertEmployeeCanReceiveNewContract($employee);

            $contractType = ContractType::findOrFail($data['contract_type_id']);
            $normalizedDates = $this->typeValidation->validateAndNormalize(
                $contractType,
                $data['start_date'],
                $data['end_date'] ?? null,
            );

            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances'], $data['status']);

            $data['contract_code'] = $data['contract_code'] ?? $this->generateCode();
            $data['end_date'] = $normalizedDates['end_date'];
            $data['previous_contract_id'] = null;
            $data['renewal_count'] = 0;
            $data['status'] = $this->resolveInitialStatus($data['start_date']);
            $data['created_by'] = $creatorId;
            $data['department_id'] = $data['department_id'] ?? $employee->department_id;
            $data['position_id'] = $data['position_id'] ?? $employee->position_id;

            $data = array_merge(
                $data,
                $this->allowanceService->applyAllowanceInput(
                    $allowanceInput,
                    $data['contract_type_id'],
                    $data['position_id'] ?? null,
                )
            );

            $this->assertNoOverlap($data['employee_id'], $data['start_date'], $data['end_date']);

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $data['file_path'] = $this->storeFile($data['contract_file'], $data['employee_id']);
            }
            unset($data['contract_file']);

            $contract = Contract::create($data);
            $this->allowanceService->syncContractAllowances($contract, $allowanceInput, $data['contract_type_id']);
            $this->historyService->logCreate($contract, $creatorId);

            return $contract;
        });
    }

    public function update(Contract $contract, array $data, ?int $performedBy = null): Contract
    {
        if (! $contract->isEditable()) {
            throw ValidationException::withMessages(['status' => 'Chỉ được cập nhật hợp đồng Draft, Pending hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data, $performedBy) {
            $original = $contract->only(ContractHistoryService::TRACKED_FIELDS);
            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $contractTypeId = $data['contract_type_id'] ?? $contract->contract_type_id;
            $contractType = ContractType::findOrFail($contractTypeId);
            $startDate = $data['start_date'] ?? $contract->start_date->toDateString();
            $endDate = array_key_exists('end_date', $data)
                ? $data['end_date']
                : $contract->end_date?->toDateString();

            $normalizedDates = $this->typeValidation->validateAndNormalize($contractType, $startDate, $endDate);
            $data['end_date'] = $normalizedDates['end_date'];
            $data['start_date'] = $startDate;

            $data = array_merge(
                $data,
                $this->allowanceService->applyAllowanceInput(
                    $allowanceInput,
                    $contractTypeId,
                    $data['position_id'] ?? $contract->position_id,
                )
            );

            $this->assertNoOverlap(
                $data['employee_id'] ?? $contract->employee_id,
                $data['start_date'],
                $data['end_date'],
                $contract->id
            );

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                if ($contract->file_path) {
                    Storage::disk('public')->delete($contract->file_path);
                }
                $data['file_path'] = $this->storeFile($data['contract_file'], $data['employee_id'] ?? $contract->employee_id);
            }
            unset($data['contract_file']);

            $contract->update($data);
            $this->allowanceService->syncContractAllowances(
                $contract,
                $allowanceInput,
                $contractTypeId
            );

            $changes = $this->historyService->collectChanges($contract, $original);
            $this->historyService->logUpdate($contract, $changes, $performedBy);

            return $contract->refresh();
        });
    }

    public function activate(Contract $contract, ?int $performedBy = null): Contract
    {
        if (! in_array($contract->status, [Contract::STATUS_DRAFT, Contract::STATUS_PENDING], true)) {
            throw ValidationException::withMessages(['status' => 'Chỉ kích hoạt được hợp đồng Đang soạn hoặc Chờ hiệu lực.']);
        }

        return DB::transaction(function () use ($contract, $performedBy) {
            $this->assertEmployeeCanReceiveNewContract($contract->employee);
            $this->assertNoOverlap(
                $contract->employee_id,
                $contract->start_date->toDateString(),
                $contract->end_date?->toDateString(),
                $contract->id
            );

            $contract->update(['status' => Contract::STATUS_ACTIVE]);
            $this->historyService->logActivate($contract->refresh(), $performedBy);

            return $contract;
        });
    }

    /**
     * Gia hạn: tạo hợp đồng mới, đánh dấu hợp đồng cũ đã thay thế.
     */
    public function extend(Contract $contract, array $data, ?int $creatorId = null): Contract
    {
        $contract->loadMissing(['employee', 'contractType']);
        $this->assertCanReplaceWithNewContract($contract);
        $this->assertExtendRenewalAllowed($contract);

        return DB::transaction(function () use ($contract, $data, $creatorId) {
            $contractTypeId = $data['contract_type_id'] ?? $contract->contract_type_id;
            $contractType = ContractType::findOrFail($contractTypeId);
            $normalizedDates = $this->typeValidation->validateAndNormalize(
                $contractType,
                $data['start_date'],
                $data['end_date'] ?? null,
            );
            $data['end_date'] = $normalizedDates['end_date'];

            $this->assertNoOverlap(
                $contract->employee_id,
                $data['start_date'],
                $data['end_date'],
                $contract->id
            );

            $actualEndDate = Carbon::parse($data['start_date'])->subDay();
            $oldEndDate = $contract->end_date;

            $contract->update([
                'status' => Contract::STATUS_REPLACED,
                'actual_end_date' => $actualEndDate,
                'end_date' => $actualEndDate,
            ]);

            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $allowanceColumns = $this->allowanceService->applyAllowanceInput(
                $allowanceInput,
                $contractTypeId,
                $contract->position_id
            );

            $payload = [
                'employee_id' => $contract->employee_id,
                'previous_contract_id' => $contract->id,
                'renewal_count' => (int) $contract->renewal_count + 1,
                'department_id' => $contract->department_id ?? $contract->employee?->department_id,
                'position_id' => $contract->position_id ?? $contract->employee?->position_id,
                'contract_type_id' => $contractTypeId,
                'contract_code' => $data['contract_code'] ?? $this->generateCode(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'salary' => $data['salary'] ?? $contract->salary,
                'allowance' => $allowanceColumns['allowance'] ?? 0,
                'allowance_meal' => $allowanceColumns['allowance_meal'] ?? 0,
                'allowance_phone' => $allowanceColumns['allowance_phone'] ?? 0,
                'allowance_fuel' => $allowanceColumns['allowance_fuel'] ?? 0,
                'allowance_position' => $allowanceColumns['allowance_position'] ?? 0,
                'description' => $data['description'] ?? $contract->description,
                'note' => $data['note'] ?? null,
                'status' => $this->resolveInitialStatus($data['start_date']),
                'created_by' => $creatorId,
            ];

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $payload['file_path'] = $this->storeFile($data['contract_file'], $contract->employee_id);
            }

            $newContract = Contract::create($payload);
            $this->allowanceService->syncContractAllowances($newContract, $allowanceInput, $contractTypeId);

            ContractExtension::create([
                'contract_id' => $contract->id,
                'new_contract_id' => $newContract->id,
                'old_end_date' => $oldEndDate,
                'new_end_date' => $data['end_date'],
                'note' => $data['note'] ?? null,
                'performed_by' => $creatorId,
            ]);

            $this->historyService->logExtend($contract, $newContract, $creatorId);

            $this->autoNotifications->clearContractExpiringNotifications($contract);
            $this->autoNotifications->notifyContractRenewed($contract, $newContract, $creatorId);

            return $newContract;
        });
    }

    public function cancel(Contract $contract, array $data, ?int $performedBy = null): Contract
    {
        if (! $contract->isCancellable()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ được hủy hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data, $performedBy) {
            $contract->update([
                'status' => Contract::STATUS_CANCELLED,
                'end_date' => $data['end_date'] ?? $contract->end_date ?? Carbon::today(),
                'actual_end_date' => $data['end_date'] ?? $contract->actual_end_date ?? Carbon::today(),
                'note' => $data['note'] ?? $contract->note,
            ]);

            $this->historyService->logCancel($contract->refresh(), $performedBy, $data['note'] ?? null);

            return $contract;
        });
    }

    public function terminate(Contract $contract, array $data, ?int $performedBy = null): Contract
    {
        if (! $contract->isCancellable()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ chấm dứt được hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data, $performedBy) {
            $endDate = $data['end_date'] ?? Carbon::today();

            $contract->update([
                'status' => Contract::STATUS_TERMINATED,
                'end_date' => $endDate,
                'actual_end_date' => $endDate,
                'note' => $data['note'] ?? $contract->note,
            ]);

            ContractTermination::create([
                'contract_id' => $contract->id,
                'reason' => $data['reason'],
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'file_path' => null,
            ]);

            $this->historyService->logTerminate(
                $contract->refresh(),
                $data['reason'],
                $performedBy,
                $data['note'] ?? null,
            );

            return $contract;
        });
    }

    /**
     * Chuyển loại HĐ (VD: thử việc → chính thức, xác định TH → không TH).
     */
    public function convertType(Contract $contract, array $data, ?int $creatorId = null): Contract
    {
        $contract->loadMissing(['employee', 'contractType']);
        $this->assertCanReplaceWithNewContract($contract);

        return DB::transaction(function () use ($contract, $data, $creatorId) {
            $contractTypeId = $data['contract_type_id'];
            $targetType = ContractType::findOrFail($contractTypeId);
            $this->conversionValidation->assertConversionAllowed($contract, $targetType);

            $effectiveDate = $data['effective_date'] ?? $data['start_date'] ?? Carbon::today()->toDateString();
            $startDate = $data['start_date'] ?? $effectiveDate;

            $normalizedDates = $this->typeValidation->validateAndNormalize(
                $targetType,
                $startDate,
                $data['end_date'] ?? null,
            );

            $this->assertNoOverlap(
                $contract->employee_id,
                $startDate,
                $normalizedDates['end_date'],
                $contract->id,
            );

            $actualEndDate = Carbon::parse($effectiveDate);
            $oldEndDate = $contract->end_date;
            $sourceTypeName = $contract->contractType?->contract_name ?? '—';

            $contract->update([
                'status' => Contract::STATUS_REPLACED,
                'actual_end_date' => $actualEndDate,
                'end_date' => $actualEndDate,
            ]);

            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $allowanceColumns = $this->allowanceService->applyAllowanceInput(
                $allowanceInput,
                $contractTypeId,
                $contract->position_id
            );

            $note = ($data['note'] ?? '') !== ''
                ? 'Chuyển loại HĐ: '.$data['note']
                : 'Chuyển loại HĐ từ '.$contract->contract_code.' ('.$sourceTypeName.' → '.$targetType->contract_name.')';

            $payload = [
                'employee_id' => $contract->employee_id,
                'previous_contract_id' => $contract->id,
                'renewal_count' => 0,
                'department_id' => $contract->department_id ?? $contract->employee?->department_id,
                'position_id' => $contract->position_id ?? $contract->employee?->position_id,
                'contract_type_id' => $contractTypeId,
                'contract_code' => $data['contract_code'] ?? $this->generateCode(),
                'start_date' => $startDate,
                'end_date' => $normalizedDates['end_date'],
                'salary' => $data['salary'] ?? $contract->salary,
                'allowance' => $allowanceColumns['allowance'] ?? 0,
                'allowance_meal' => $allowanceColumns['allowance_meal'] ?? 0,
                'allowance_phone' => $allowanceColumns['allowance_phone'] ?? 0,
                'allowance_fuel' => $allowanceColumns['allowance_fuel'] ?? 0,
                'allowance_position' => $allowanceColumns['allowance_position'] ?? 0,
                'description' => $data['description'] ?? $contract->description,
                'note' => $note,
                'status' => Contract::STATUS_ACTIVE,
                'created_by' => $creatorId,
            ];

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $payload['file_path'] = $this->storeFile($data['contract_file'], $contract->employee_id);
            }

            $newContract = Contract::create($payload);
            $this->allowanceService->syncContractAllowances($newContract, $allowanceInput, $contractTypeId);

            ContractExtension::create([
                'contract_id' => $contract->id,
                'new_contract_id' => $newContract->id,
                'old_end_date' => $oldEndDate,
                'new_end_date' => $normalizedDates['end_date'],
                'note' => $note,
                'performed_by' => $creatorId,
            ]);

            $this->historyService->logConvert(
                $contract,
                $newContract,
                $sourceTypeName,
                $targetType->contract_name,
                $creatorId,
            );

            $this->autoNotifications->clearContractExpiringNotifications($contract);
            $this->autoNotifications->notifyContractConverted($contract, $newContract, $creatorId);

            return $newContract;
        });
    }

    public function softDelete(Contract $contract, ?int $performedBy = null): void
    {
        if ($contract->status === Contract::STATUS_ACTIVE) {
            throw ValidationException::withMessages(['contract' => 'Không được xóa hợp đồng đang Active.']);
        }

        $this->historyService->logDelete($contract, $performedBy);
        $contract->delete();
    }

    public function restore(Contract $contract, ?int $performedBy = null): Contract
    {
        return DB::transaction(function () use ($contract, $performedBy) {
            $hasActive = Contract::query()
                ->forEmployee($contract->employee_id)
                ->active()
                ->exists();

            $contract->restore();

            if ($hasActive) {
                $contract->update(['status' => Contract::STATUS_DRAFT]);
            }

            $this->historyService->logRestore($contract->refresh(), $performedBy);

            return $contract;
        });
    }

    public function forceDelete(Contract $contract, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($contract, $performedBy) {
            $this->historyService->logForceDelete($contract, $performedBy);

            if ($contract->file_path) {
                Storage::disk('public')->delete($contract->file_path);
            }
            $contract->forceDelete();
        });
    }

    public function autoExpire(): int
    {
        return Contract::query()
            ->active()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', Carbon::today())
            ->update(['status' => Contract::STATUS_EXPIRED]);
    }

    public function activatePendingContracts(): int
    {
        $pending = Contract::query()
            ->where('status', Contract::STATUS_PENDING)
            ->whereDate('start_date', '<=', Carbon::today())
            ->get();

        $activated = 0;

        foreach ($pending as $contract) {
            try {
                $this->activate($contract);
                $activated++;
            } catch (ValidationException) {
                // Bỏ qua nếu nhân viên đã có HĐ active khác.
            }
        }

        return $activated;
    }

    protected function assertEmployeeCanReceiveNewContract(Employee $employee): void
    {
        if ($employee->status !== 'active') {
            throw ValidationException::withMessages([
                'employee_id' => 'Nhân viên không còn hoạt động.',
            ]);
        }

        $hasActiveContract = Contract::query()
            ->forEmployee($employee->id)
            ->active()
            ->exists();

        if ($hasActiveContract) {
            throw ValidationException::withMessages([
                'employee_id' => 'Nhân viên đã có hợp đồng hiệu lực, vui lòng gia hạn/chuyển loại thay vì tạo mới',
            ]);
        }
    }

    protected function assertCanReplaceWithNewContract(Contract $contract): void
    {
        if ($contract->status === Contract::STATUS_REPLACED) {
            throw ValidationException::withMessages([
                'contract' => 'Hợp đồng này đã được thay thế.',
            ]);
        }

        if (! $contract->canBeExtended()) {
            throw ValidationException::withMessages([
                'contract' => 'Chỉ thao tác được với hợp đồng đang còn hiệu lực hoặc sắp hết hạn.',
            ]);
        }
    }

    protected function assertExtendRenewalAllowed(Contract $contract): void
    {
        if ($contract->isFixedTermRenewalBlocked()) {
            throw ValidationException::withMessages([
                'contract' => Contract::fixedTermRenewalBlockedMessage(),
            ]);
        }
    }

    protected function assertCanExtend(Contract $contract): void
    {
        $this->assertCanReplaceWithNewContract($contract);
        $this->assertExtendRenewalAllowed($contract);
    }

    protected function resolveInitialStatus(string $startDate): string
    {
        return Carbon::parse($startDate)->startOfDay()->lte(Carbon::today())
            ? Contract::STATUS_ACTIVE
            : Contract::STATUS_PENDING;
    }

    protected function assertNoOverlap(int $employeeId, string $startDate, ?string $endDate, ?int $ignoreId = null): void
    {
        $rule = new NoContractOverlap($employeeId, $startDate, $endDate, $ignoreId);
        $rule->validate('start_date', null, function (string $message) {
            throw ValidationException::withMessages(['start_date' => $message]);
        });
    }

    protected function storeFile(UploadedFile $file, int $employeeId): string
    {
        $timestamp = Carbon::now()->timestamp;
        $extension = $file->getClientOriginalExtension();
        $safeExt = Str::lower($extension);

        $filename = "contract_{$employeeId}_{$timestamp}.{$safeExt}";

        return $file->storeAs('contracts', $filename, 'public');
    }
}
