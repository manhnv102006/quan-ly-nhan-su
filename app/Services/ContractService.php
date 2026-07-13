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

    public function create(array $data, ?int $creatorId = null): Contract
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $employee = Employee::findOrFail($data['employee_id']);
            if ($employee->status !== 'active') {
                throw ValidationException::withMessages(['employee_id' => 'Nhân viên không còn hoạt động.']);
            }

            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $data['contract_code'] = $data['contract_code'] ?? $this->generateCode();
            $data['status'] = $data['status'] ?? Contract::STATUS_ACTIVE;
            $data['created_by'] = $creatorId;
            $data['department_id'] = $data['department_id'] ?? $employee->department_id;
            $data['position_id'] = $data['position_id'] ?? $employee->position_id;

            $data = array_merge(
                $data,
                $this->allowanceService->applyAllowanceInput(
                    $allowanceInput,
                    $data['contract_type_id'] ?? null,
                    $data['position_id'] ?? null,
                )
            );

            $this->assertNoOverlap($data['employee_id'], $data['start_date'], $data['end_date']);

            if ($data['status'] === Contract::STATUS_ACTIVE) {
                $this->deactivateActiveContract($data['employee_id']);
            }

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $data['file_path'] = $this->storeFile($data['contract_file'], $data['employee_id']);
            }
            unset($data['contract_file']);

            $contract = Contract::create($data);
            $this->allowanceService->syncContractAllowances($contract, $allowanceInput, $data['contract_type_id'] ?? null);

            return $contract;
        });
    }

    public function update(Contract $contract, array $data): Contract
    {
        if (! $contract->isEditable()) {
            throw ValidationException::withMessages(['status' => 'Chỉ được cập nhật hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data) {
            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $data = array_merge(
                $data,
                $this->allowanceService->applyAllowanceInput(
                    $allowanceInput,
                    $data['contract_type_id'] ?? $contract->contract_type_id,
                    $data['position_id'] ?? $contract->position_id,
                )
            );

            $this->assertNoOverlap(
                $data['employee_id'] ?? $contract->employee_id,
                $data['start_date'] ?? $contract->start_date->toDateString(),
                $data['end_date'] ?? $contract->end_date?->toDateString(),
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
                $data['contract_type_id'] ?? $contract->contract_type_id
            );

            return $contract->refresh();
        });
    }

    public function activate(Contract $contract): Contract
    {
        if ($contract->status !== Contract::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Chỉ kích hoạt được hợp đồng ở trạng thái Đang soạn.']);
        }

        return DB::transaction(function () use ($contract) {
            $this->assertNoOverlap(
                $contract->employee_id,
                $contract->start_date->toDateString(),
                $contract->end_date?->toDateString(),
                $contract->id
            );

            $this->deactivateActiveContract($contract->employee_id);
            $contract->update(['status' => Contract::STATUS_ACTIVE]);

            return $contract->refresh();
        });
    }

    /**
     * Gia hạn: tạo hợp đồng mới, hết hiệu lực hợp đồng cũ.
     */
    public function extend(Contract $contract, array $data, ?int $creatorId = null): Contract
    {
        if (! $contract->isEditable()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ được gia hạn hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data, $creatorId) {
            $this->assertNoOverlap(
                $contract->employee_id,
                $data['start_date'],
                $data['end_date']
            );

            $oldEndDate = $contract->end_date;
            $contract->update([
                'status' => Contract::STATUS_EXPIRED,
                'end_date' => $data['start_date'],
            ]);

            ContractExtension::create([
                'contract_id' => $contract->id,
                'old_end_date' => $oldEndDate,
                'new_end_date' => $data['end_date'],
                'note' => $data['note'] ?? null,
            ]);

            $allowanceInput = $data['allowances'] ?? [];
            unset($data['allowances']);

            $contractTypeId = $data['contract_type_id'] ?? $contract->contract_type_id;
            $allowanceColumns = $this->allowanceService->applyAllowanceInput(
                $allowanceInput,
                $contractTypeId,
                $contract->position_id
            );

            $payload = [
                'employee_id' => $contract->employee_id,
                'department_id' => $contract->department_id ?? $contract->employee?->department_id,
                'position_id' => $contract->position_id ?? $contract->employee?->position_id,
                'contract_type_id' => $contractTypeId,
                'contract_code' => $data['contract_code'] ?? $this->generateCode(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'salary' => $data['salary'] ?? $contract->salary,
                'allowance' => $allowanceColumns['allowance'] ?? $this->allowanceForContractType($contractTypeId),
                'allowance_meal' => $allowanceColumns['allowance_meal'] ?? $contract->allowance_meal,
                'allowance_phone' => $allowanceColumns['allowance_phone'] ?? $contract->allowance_phone,
                'allowance_fuel' => $allowanceColumns['allowance_fuel'] ?? $contract->allowance_fuel,
                'allowance_position' => $allowanceColumns['allowance_position'] ?? $contract->allowance_position,
                'description' => $data['description'] ?? $contract->description,
                'note' => $data['note'] ?? null,
                'status' => Contract::STATUS_ACTIVE,
                'created_by' => $creatorId,
            ];

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $payload['file_path'] = $this->storeFile($data['contract_file'], $contract->employee_id);
            }

            $newContract = Contract::create($payload);
            $this->allowanceService->syncContractAllowances($newContract, $allowanceInput, $contractTypeId);

            return $newContract;
        });
    }

    public function cancel(Contract $contract, array $data): Contract
    {
        if (! $contract->isCancellable()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ được hủy hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data) {
            $contract->update([
                'status' => Contract::STATUS_CANCELLED,
                'end_date' => $data['end_date'] ?? $contract->end_date ?? Carbon::today(),
                'note' => $data['note'] ?? $contract->note,
            ]);

            return $contract->refresh();
        });
    }

    public function terminate(Contract $contract, array $data): Contract
    {
        if (! $contract->isCancellable()) {
            throw ValidationException::withMessages(['contract' => 'Chỉ chấm dứt được hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data) {
            $endDate = $data['end_date'] ?? Carbon::today();

            $contract->update([
                'status' => Contract::STATUS_TERMINATED,
                'end_date' => $endDate,
                'note' => $data['note'] ?? $contract->note,
            ]);

            ContractTermination::create([
                'contract_id' => $contract->id,
                'reason' => $data['reason'],
                'end_date' => $endDate,
                'note' => $data['note'] ?? null,
                'file_path' => null,
            ]);

            return $contract->refresh();
        });
    }

    /**
     * Chuyển loại HĐ (VD: thử việc → chính thức): tạo HĐ mới, HĐ cũ hết hiệu lực.
     */
    public function convertType(Contract $contract, array $data, ?int $creatorId = null): Contract
    {
        $data['note'] = ($data['note'] ?? '') !== ''
            ? 'Chuyển loại HĐ: '.$data['note']
            : 'Chuyển loại HĐ từ '.$contract->contract_code;

        return $this->extend($contract, $data, $creatorId);
    }

    public function softDelete(Contract $contract): void
    {
        if ($contract->status === Contract::STATUS_ACTIVE) {
            throw ValidationException::withMessages(['contract' => 'Không được xóa hợp đồng đang Active.']);
        }
        $contract->delete();
    }

    public function restore(Contract $contract): Contract
    {
        return DB::transaction(function () use ($contract) {
            $hasActive = Contract::query()
                ->forEmployee($contract->employee_id)
                ->active()
                ->exists();

            $contract->restore();

            if ($hasActive) {
                $contract->update(['status' => Contract::STATUS_DRAFT]);
            }

            return $contract->refresh();
        });
    }

    public function forceDelete(Contract $contract): void
    {
        DB::transaction(function () use ($contract) {
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

    protected function assertNoOverlap(int $employeeId, string $startDate, ?string $endDate, ?int $ignoreId = null): void
    {
        $rule = new NoContractOverlap($employeeId, $startDate, $endDate, $ignoreId);
        $rule->validate('start_date', null, function (string $message) {
            throw ValidationException::withMessages(['start_date' => $message]);
        });
    }

    protected function deactivateActiveContract(int $employeeId): void
    {
        Contract::query()
            ->forEmployee($employeeId)
            ->active()
            ->update(['status' => Contract::STATUS_EXPIRED]);
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
