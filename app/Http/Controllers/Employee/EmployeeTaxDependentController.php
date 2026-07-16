<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Concerns\ResolvesLinkedEmployee;
use App\Http\Controllers\Controller;
use App\Models\TaxDependent;
use App\Services\TaxDependentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeTaxDependentController extends Controller
{
    use ResolvesLinkedEmployee;

    public function __construct(
        private readonly TaxDependentRegistrationService $registrations,
    ) {}

    public function index(): View
    {
        $employee = $this->linkedEmployee();

        $dependents = TaxDependent::query()
            ->where('employee_id', $employee->id)
            ->with(['approver', 'rejecter'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('employee.tax-dependents.index', [
            'dependents' => $dependents,
            'summary' => $this->registrations->employeeSummary($employee),
        ]);
    }

    public function create(): View
    {
        return view('employee.tax-dependents.create', [
            'relationshipLabels' => TaxDependent::RELATIONSHIP_LABELS,
            'defaultDeduction' => TaxDependent::DEFAULT_MONTHLY_DEDUCTION,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->linkedEmployee();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:child,spouse,parent,other',
            'date_of_birth' => 'nullable|date',
            'id_number' => 'nullable|string|max:30',
            'start_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên người phụ thuộc.',
            'relationship.required' => 'Vui lòng chọn quan hệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu giảm trừ.',
        ]);

        $validated['monthly_deduction'] = TaxDependent::DEFAULT_MONTHLY_DEDUCTION;

        try {
            $this->registrations->submitRequest($employee, $validated, (int) auth()->id());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->withErrors(['full_name' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('employee.tax-dependents.index')
            ->with('success', 'Đã gửi đăng ký NPT tới kế toán. Sau khi duyệt, người phụ thuộc sẽ được áp dụng giảm trừ thuế (GT phụ thuộc) ngay.');
    }

    public function show(TaxDependent $taxDependent): View
    {
        $employee = $this->linkedEmployee();
        abort_unless($taxDependent->employee_id === $employee->id, 403);

        $taxDependent->load(['approver', 'rejecter', 'requester']);

        return view('employee.tax-dependents.show', [
            'dependent' => $taxDependent,
        ]);
    }
}
