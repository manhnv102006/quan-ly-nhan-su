<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTaxProfile;
use App\Models\PayrollPeriod;
use App\Models\TaxDependent;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function __construct(
        private readonly TaxService $tax,
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
        $rows = $period ? $this->tax->calculateForPeriod($period) : collect();

        return view('accountant.tax.index', [
            'periods' => $periods,
            'period' => $period,
            'rows' => $rows,
            'totalPit' => (float) $rows->sum('pit'),
            'totalGross' => (float) $rows->sum('gross'),
        ]);
    }

    public function dependents(Request $request): View
    {
        $employeeId = $request->integer('employee_id');

        $employees = Employee::query()
            ->whereIn('status', ['active', 'inactive'])
            ->withCount(['taxDependents as active_dependents_count' => fn ($q) => $q->where('is_active', true)])
            ->with('taxProfile')
            ->orderBy('full_name')
            ->get();

        $selectedEmployee = $employeeId ? Employee::with(['taxProfile', 'taxDependents'])->find($employeeId) : null;
        $dependents = $selectedEmployee?->taxDependents()->orderByDesc('is_active')->orderBy('full_name')->get() ?? collect();

        return view('accountant.tax.dependents', compact('employees', 'selectedEmployee', 'dependents'));
    }

    public function storeDependent(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $this->validateDependent($request);
        $validated['employee_id'] = $employee->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        TaxDependent::create($validated);
        $this->ensureTaxProfile($employee);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã thêm người phụ thuộc.');
    }

    public function updateDependent(Request $request, Employee $employee, TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->employee_id === $employee->id, 404);

        $validated = $this->validateDependent($request);
        $validated['is_active'] = $request->boolean('is_active');

        $dependent->update($validated);

        return redirect()
            ->route('accountant.tax.dependents', ['employee_id' => $employee->id])
            ->with('success', 'Đã cập nhật người phụ thuộc.');
    }

    public function destroyDependent(Employee $employee, TaxDependent $dependent): RedirectResponse
    {
        abort_unless($dependent->employee_id === $employee->id, 404);
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

        EmployeeTaxProfile::updateOrCreate(
            ['employee_id' => $employee->id],
            $validated
        );

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
        if (! $employee->taxProfile) {
            EmployeeTaxProfile::create(['employee_id' => $employee->id]);
        }
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
            'id_number' => 'nullable|string|max:30',
            'monthly_deduction' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:1000',
        ]);
    }
}
