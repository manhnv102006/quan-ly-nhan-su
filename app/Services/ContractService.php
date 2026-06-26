<?php

namespace App\Services;

use App\Models\Contract;
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

            $data['contract_code'] = $data['contract_code'] ?? $this->generateCode();
            $data['status'] = Contract::STATUS_ACTIVE;
            $data['created_by'] = $creatorId;

            $this->assertNoOverlap($data['employee_id'], $data['start_date'], $data['end_date']);

            $this->deactivateActiveContract($data['employee_id']);

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $data['file_path'] = $this->storeFile($data['contract_file'], $data['employee_id']);
            }
            unset($data['contract_file']);

            return Contract::create($data);
        });
    }

    public function update(Contract $contract, array $data): Contract
    {
        if (! $contract->isEditable()) {
            throw ValidationException::withMessages(['status' => 'Chỉ được cập nhật hợp đồng Draft hoặc Active.']);
        }

        return DB::transaction(function () use ($contract, $data) {
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

            $contract->update([
                'status' => Contract::STATUS_EXPIRED,
                'end_date' => $data['start_date'],
            ]);

            $payload = [
                'employee_id' => $contract->employee_id,
                'department_id' => $contract->department_id,
                'position_id' => $contract->position_id,
                'contract_type_id' => $data['contract_type_id'] ?? $contract->contract_type_id,
                'contract_code' => $data['contract_code'] ?? $this->generateCode(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'salary' => $data['salary'] ?? $contract->salary,
                'allowance' => $data['allowance'] ?? $contract->allowance,
                'description' => $data['description'] ?? $contract->description,
                'note' => $data['note'] ?? null,
                'status' => Contract::STATUS_ACTIVE,
                'created_by' => $creatorId,
            ];

            if (isset($data['contract_file']) && $data['contract_file'] instanceof UploadedFile) {
                $payload['file_path'] = $this->storeFile($data['contract_file'], $contract->employee_id);
            }

            return Contract::create($payload);
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
                'end_date' => $data['end_date'] ?? $contract->end_date,
                'note' => $data['note'] ?? $contract->note,
            ]);

            return $contract->refresh();
        });
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
