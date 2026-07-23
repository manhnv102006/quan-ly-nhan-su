<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTaxProfile;
use App\Models\TaxDependent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaxDependentRegistrationService
{
    public function __construct(
        private readonly ModuleChangeLogService $changeLogs,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitRequest(Employee $employee, array $data, int $requestedBy): TaxDependent
    {
        abort_unless($employee->status === 'active', 422, 'Chỉ nhân viên đang làm việc mới được đăng ký NPT.');

        $fullName = trim((string) $data['full_name']);
        $idNumber = isset($data['id_number']) ? trim((string) $data['id_number']) : null;

        $duplicateQuery = TaxDependent::query()
            ->where('employee_id', $employee->id)
            ->where('status', TaxDependent::STATUS_PENDING)
            ->where('full_name', $fullName);

        if ($idNumber !== null && $idNumber !== '') {
            $duplicateQuery->where('id_number', $idNumber);
        }

        abort_if(
            $duplicateQuery->exists(),
            422,
            'Bạn đã có đăng ký NPT chờ duyệt cho người này. Vui lòng chờ kế toán xử lý.'
        );

        return DB::transaction(function () use ($employee, $data, $requestedBy, $fullName, $idNumber) {
            $dependent = TaxDependent::create([
                'employee_id' => $employee->id,
                'full_name' => $fullName,
                'relationship' => $data['relationship'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'id_number' => $idNumber ?: null,
                'monthly_deduction' => $data['monthly_deduction'] ?? app(TaxService::class)->defaultDependentDeduction(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => TaxDependent::STATUS_PENDING,
                'is_active' => false,
                'requested_by' => $requestedBy,
            ]);

            $this->ensureTaxProfile($employee);
            $this->changeLogs->logTaxDependentCreate($dependent, $requestedBy);

            return $dependent;
        });
    }

    public function approve(TaxDependent $dependent, ?int $userId = null): void
    {
        abort_unless($dependent->canBeApproved(), 422, 'Đăng ký NPT không ở trạng thái chờ duyệt.');

        $original = $dependent->only(['status', 'is_active']);
        $userId = $userId ?? (int) auth()->id();

        $dependent->update([
            'status' => TaxDependent::STATUS_APPROVED,
            'is_active' => true,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $this->ensureTaxProfile($dependent->employee);
        $this->changeLogs->logTaxDependentUpdate($dependent->fresh(), $original, $userId);
    }

    public function reject(TaxDependent $dependent, string $reason, ?int $userId = null): void
    {
        abort_unless($dependent->canBeRejected(), 422, 'Đăng ký NPT không ở trạng thái chờ duyệt.');

        $original = $dependent->only(['status', 'is_active']);
        $userId = $userId ?? (int) auth()->id();

        $dependent->update([
            'status' => TaxDependent::STATUS_REJECTED,
            'is_active' => false,
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->changeLogs->logTaxDependentUpdate($dependent->fresh(), $original, $userId);
    }

    /**
     * @return Collection<int, TaxDependent>
     */
    public function pendingRegistrations(): Collection
    {
        return TaxDependent::query()
            ->where('status', TaxDependent::STATUS_PENDING)
            ->with(['employee.department', 'requester'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return array{pending: int, approved: int, rejected: int, active: int}
     */
    public function employeeSummary(Employee $employee): array
    {
        $dependents = TaxDependent::query()->where('employee_id', $employee->id)->get();

        return [
            'pending' => $dependents->where('status', TaxDependent::STATUS_PENDING)->count(),
            'approved' => $dependents->where('status', TaxDependent::STATUS_APPROVED)->count(),
            'rejected' => $dependents->where('status', TaxDependent::STATUS_REJECTED)->count(),
            'active' => $dependents->filter(fn (TaxDependent $d) => $d->countsForTaxDeduction())->count(),
        ];
    }

    public function pendingCount(): int
    {
        return TaxDependent::query()->where('status', TaxDependent::STATUS_PENDING)->count();
    }

    private function ensureTaxProfile(Employee $employee): void
    {
        if ($employee->taxProfile) {
            return;
        }

        $policy = \App\Models\TaxPolicy::current();

        EmployeeTaxProfile::create([
            'employee_id' => $employee->id,
            'personal_deduction' => $policy?->personal_deduction ?? EmployeeTaxProfile::DEFAULT_PERSONAL_DEDUCTION,
        ]);
    }
}
