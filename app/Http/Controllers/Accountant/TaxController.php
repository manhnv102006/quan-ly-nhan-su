<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeTaxProfile;
use App\Models\PayrollPeriod;
use App\Models\TaxDependent;
use App\Models\TaxDependentDocument;
use App\Models\TaxPolicy;
use App\Rules\CitizenIdNumber;
use App\Services\TaxDependentDocumentService;
use App\Services\TaxDependentRegistrationService;
use App\Services\TaxService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function __construct(
        private readonly TaxService $tax,
        private readonly ModuleChangeLogService $changeLogs,
        private readonly TaxDependentRegistrationService $registrations,
        private readonly TaxDependentDocumentService $dependentDocuments,
    ) {}

    public function index(Request $request): View
    {
        $periodId = $request->integer('period_id') ?: PayrollPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->value('id');

        $periods = PayrollPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(24)
            ->get();

        $period = $periodId ? PayrollPeriod::find($periodId) : null;

        $filters = [
            'department_id' => $request->input('department_id'),
            'search' => $request->input('search'),
            'pit_filter' => $request->input('pit_filter'),
        ];

        $departments = Department::query()
            ->where('status', 'active')
            ->orderBy('department_name')
            ->get(['id', 'department_code', 'department_name']);

        $allRows = $period ? $this->tax->calculateForPeriod($period) : collect();
        $rows = $this->tax->filterPeriodRows($allRows, $filters);

        $periodTaxPolicy = $period
            ? $this->tax->policyForDate(Carbon::create((int) $period->year, (int) $period->month, 15))
            : null;

        return view('accountant.tax.index', [
            'periods' => $periods,
            'period' => $period,
            'departments' => $departments,
            'filters' => $filters,
            'rows' => $rows,
            'totalPit' => (float) $rows->sum('pit'),
            'totalGross' => (float) $rows->sum('gross'),
            'periodTaxPolicy' => $periodTaxPolicy,
            'currentTaxPolicy' => TaxPolicy::current(),
        ]);
    }

    public function dependents(Request $request): View
    {
        $employeeId = $request->integer('employee_id');

        $employees = Employee::query()
            ->whereIn('status', ['active', 'inactive'])
            ->withCount(['taxDependents as active_dependents_count' => fn ($q) => $q
                ->where('status', TaxDependent::STATUS_APPROVED)
                ->where('is_active', true)])
            ->with('taxProfile')
            ->orderBy('full_name')
            ->get();

        $selectedEmployee = $employeeId ? Employee::with(['taxProfile', 'taxDependents'])->find($employeeId) : null;
        $dependents = $selectedEmployee?->taxDependents()->orderByDesc('is_active')->orderBy('full_name')->get() ?? collect();

        return view('accountant.tax.dependents', compact('employees', 'selectedEmployee', 'dependents'));
    }

    public function storeDependent(Request $request, Employee $employee): RedirectResponse
    {
        if (! $this->registrations->canEmployeeRegister($employee)) {
            return redirect()
                ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
                ->with('error', 'Mỗi nhân viên chỉ được 1 NPT (chờ duyệt hoặc đã duyệt).');
        }

        $request->merge(['id_number' => CitizenIdNumber::normalize($request->input('id_number'))]);

        $validated = $this->validateDependent($request);
        $this->registrations->assertIdNumberAvailableForEmployee($employee, $validated['id_number']);

        $validated['employee_id'] = $employee->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['status'] = TaxDependent::STATUS_APPROVED;
        $validated['approved_by'] = auth()->id();
        $validated['approved_at'] = now();

        $dependent = TaxDependent::create($validated);
        $this->ensureTaxProfile($employee);
        $this->changeLogs->logTaxDependentCreate($dependent);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã thêm người phụ thuộc.');
    }

    public function updateDependent(Request $request, Employee $employee, TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->employee_id === $employee->id, 404);

        $request->merge(['id_number' => CitizenIdNumber::normalize($request->input('id_number'))]);

        $validated = $this->validateDependent($request);
        $this->registrations->assertIdNumberAvailableForEmployee($employee, $validated['id_number'], $dependent->id);
        $original = $dependent->only(array_keys(ModuleChangeLogService::TAX_DEPENDENT_FIELDS));
        $validated['is_active'] = $request->boolean('is_active');

        $dependent->update($validated);
        $this->changeLogs->logTaxDependentUpdate($dependent, $original);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã cập nhật người phụ thuộc.');
    }

    public function destroyDependent(Employee $employee, TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->employee_id === $employee->id, 404);
        $this->changeLogs->logTaxDependentDelete($dependent);
        $dependent->delete();

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã xóa người phụ thuộc.');
    }

    public function updateProfile(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'tax_code' => 'nullable|string|max:20',
            'personal_deduction' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:2000',
        ]);

        $profile = EmployeeTaxProfile::query()->firstOrNew(['employee_id' => $employee->id]);
        $original = $profile->exists
            ? $profile->only(array_keys(ModuleChangeLogService::TAX_PROFILE_FIELDS))
            : [];

        $profile->fill($validated);
        $profile->employee_id = $employee->id;
        $profile->save();

        $this->changeLogs->logTaxProfileUpdate($profile, $original, $employee->id);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã cập nhật hồ sơ thuế.');
    }

    public function declaration(Request $request): View
    {
        $type = $request->input('type', 'month');
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $quarter = (int) $request->input('quarter', (int) ceil(now()->month / 3));

        [$start, $end, $label] = $this->tax->periodRange($type, $year, $month, $quarter);
        $periods = $this->tax->periodsInRange($start, $end);

        $rows = collect();
        foreach ($periods as $period) {
            foreach ($this->tax->calculateForPeriod($period) as $row) {
                $rows->push(array_merge($row, ['period' => $period]));
            }
        }

        $totals = [
            'gross' => $rows->sum('gross'),
            'pit' => $rows->sum('pit'),
            'employees' => $rows->pluck('employee.id')->unique()->count(),
        ];

        return view('accountant.tax.declaration', compact('type', 'year', 'month', 'quarter', 'label', 'rows', 'totals', 'start', 'end'));
    }

    public function exportDeclaration(Request $request): Response
    {
        $type = $request->input('type', 'month');
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $quarter = (int) $request->input('quarter', (int) ceil(now()->month / 3));

        [$start, $end, $label] = $this->tax->periodRange($type, $year, $month, $quarter);
        $periods = $this->tax->periodsInRange($start, $end);
        $report = $this->tax->buildDeclarationRows($periods);
        $csv = $this->tax->toCsv($report);

        $filename = 'to_khai_tncn_'.str_replace([' ', '/'], '_', $label).'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function pendingRegistrations(): View
    {
        $pending = $this->registrations->pendingRegistrations();

        return view('accountant.tax.pending-registrations', [
            'pending' => $pending,
            'pendingCount' => $pending->count(),
        ]);
    }

    public function approveRegistration(TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->status === TaxDependent::STATUS_PENDING, 404);

        $hasOther = TaxDependent::query()
            ->where('employee_id', $dependent->employee_id)
            ->where('status', TaxDependent::STATUS_APPROVED)
            ->where('id', '!=', $dependent->id)
            ->exists();

        if ($hasOther) {
            return redirect()
                ->route('accountant.tax.pending-registrations')
                ->with('error', 'Nhân viên này đã có NPT được duyệt. Mỗi nhân viên chỉ được 1 NPT.');
        }

        $employeeId = $dependent->employee_id;
        $this->registrations->approve($dependent);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employeeId])
            ->with('success', 'Đã duyệt đăng ký NPT. Người phụ thuộc đã được áp dụng giảm trừ thuế (GT phụ thuộc).');
    }

    public function rejectRegistration(Request $request, TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->status === TaxDependent::STATUS_PENDING, 404);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $this->registrations->reject($dependent, $validated['rejection_reason']);

        return redirect()
            ->route('accountant.tax.pending-registrations')
            ->with('success', 'Đã từ chối đăng ký NPT.');
    }

    public function downloadDependentDocument(TaxDependent $dependent, TaxDependentDocument $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless((int) $document->tax_dependent_id === (int) $dependent->id, 404);

        $role = auth()->user()?->role?->name;
        abort_unless($this->dependentDocuments->userCanDownload($document, (int) auth()->id(), $role), 403);

        return $this->dependentDocuments->downloadResponse($document);
    }

    public function settlement(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $rows = $this->tax->yearSettlement($year);

        $totals = [
            'withheld' => $rows->sum('pit_withheld'),
            'liability' => $rows->sum('pit_liability'),
            'refund' => $rows->where('settlement_status', 'refund')->sum(fn ($r) => abs($r['difference'])),
            'pay_more' => $rows->where('settlement_status', 'pay_more')->sum(fn ($r) => abs($r['difference'])),
        ];

        return view('accountant.tax.settlement', compact('year', 'rows', 'totals'));
    }

    public function exportSettlement(Request $request): Response
    {
        $year = (int) $request->input('year', now()->year);
        $rows = $this->tax->yearSettlement($year);

        $headers = [
            'Mã NV', 'Họ tên', 'MST', 'Số tháng', 'Tổng thu nhập', 'Tổng BH NLĐ',
            'GT bản thân/năm', 'GT phụ thuộc/năm', 'TN tính thuế/năm',
            'Thuế đã khấu trừ', 'Thuế phải nộp/năm', 'Chênh lệch', 'Kết quả QT',
        ];

        $data = ['headers' => $headers, 'rows' => []];
        foreach ($rows as $row) {
            $status = match ($row['settlement_status']) {
                'refund' => 'Hoàn thuế',
                'pay_more' => 'Nộp thêm',
                default => 'Đủ thuế',
            };
            $data['rows'][] = [
                $row['employee']?->employee_code ?? '',
                $row['employee']?->full_name ?? '',
                $row['employee']?->taxProfile?->tax_code ?? '',
                $row['months_count'],
                $row['total_gross'],
                $row['total_insurance'],
                $row['personal_annual'],
                $row['dependent_annual'],
                $row['taxable_annual'],
                $row['pit_withheld'],
                $row['pit_liability'],
                $row['difference'],
                $status,
            ];
        }

        $csv = $this->tax->toCsv($data);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="quyet_toan_tncn_'.$year.'.csv"',
        ]);
    }

    private function ensureTaxProfile(Employee $employee): void
    {
        if ($employee->taxProfile) {
            return;
        }

        $policy = TaxPolicy::current();

        EmployeeTaxProfile::create([
            'employee_id' => $employee->id,
            'personal_deduction' => $policy?->personal_deduction ?? EmployeeTaxProfile::DEFAULT_PERSONAL_DEDUCTION,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDependent(Request $request): array
    {
        return $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:child,spouse,parent,other',
            'date_of_birth' => 'nullable|date',
            'id_number' => ['required', 'string', new CitizenIdNumber],
            'monthly_deduction' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:1000',
        ], [
            'id_number.required' => 'Vui lòng nhập số CCCD/CMND.',
        ]);
    }
}
