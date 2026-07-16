<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Department;
use App\Services\ContractAllowanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        protected ContractAllowanceService $allowanceService,
    ) {}
    public function index(Request $request): View
    {
        if ($request->filled('department_id')) {
            return $this->departmentEmployees(Department::findOrFail($request->department_id), $request);
        }

        if ($request->filled('employee_id')) {
            return $this->employeeContracts($request->integer('employee_id'), $request);
        }

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (Department $department) {
                $employeeIds = $department->employees()->pluck('id');
                $department->contracts_count = $employeeIds->isEmpty()
                    ? 0
                    : Contract::query()->whereIn('employee_id', $employeeIds)->count();
                $department->active_contracts_count = $employeeIds->isEmpty()
                    ? 0
                    : Contract::query()
                        ->whereIn('employee_id', $employeeIds)
                        ->where('status', Contract::STATUS_ACTIVE)
                        ->count();
                $department->expiring_count = $employeeIds->isEmpty()
                    ? 0
                    : $this->expiringQuery(30)
                        ->whereIn('employee_id', $employeeIds)
                        ->count();

                return $department;
            });

        $expiringSoon = $this->expiringQuery(30)
            ->with(['employee.department', 'contractType'])
            ->orderBy('end_date')
            ->limit(8)
            ->get();

        return view('accountant.contracts.index', [
            'departments' => $departments,
            'expiringSoon' => $expiringSoon,
            'expiringCount' => $this->expiringQuery(30)->count(),
        ]);
    }

    public function salaryOverview(Request $request): View
    {
        $query = Contract::query()
            ->with(['employee.department', 'employee.position', 'contractType'])
            ->where('status', Contract::STATUS_ACTIVE);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_code', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($e) => $e
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn ($e) => $e->where('department_id', $request->department_id));
        }

        $contracts = $query->orderByDesc('salary')->paginate(20)->withQueryString();

        $contracts->getCollection()->transform(function (Contract $contract) {
            $allowance = $this->allowanceService->totalAllowance($contract);
            $contract->computed_allowance = $allowance;
            $contract->computed_total_income = (float) $contract->salary + $allowance;

            return $contract;
        });

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();

        return view('accountant.contracts.salary-overview', [
            'contracts' => $contracts,
            'departments' => $departments,
        ]);
    }

    public function expiring(Request $request): View
    {
        $days = max(7, min(90, (int) $request->input('days', 30)));

        $contracts = $this->expiringQuery($days)
            ->with(['employee.department', 'employee.position', 'contractType'])
            ->orderBy('end_date')
            ->get()
            ->map(function (Contract $contract) {
                $allowance = $this->allowanceService->totalAllowance($contract);

                return [
                    'contract' => $contract,
                    'days_left' => (int) now()->startOfDay()->diffInDays($contract->end_date, false),
                    'total_income' => (float) $contract->salary + $allowance,
                    'allowance' => $allowance,
                ];
            });

        $stats = [
            'within_7' => $this->expiringQuery(7)->count(),
            'within_15' => $this->expiringQuery(15)->count(),
            'within_30' => $this->expiringQuery(30)->count(),
            'within_60' => $this->expiringQuery(60)->count(),
        ];

        return view('accountant.contracts.expiring', compact('contracts', 'days', 'stats'));
    }

    protected function expiringQuery(int $withinDays): \Illuminate\Database\Eloquent\Builder
    {
        return Contract::query()
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>', now())
            ->whereDate('end_date', '<=', now()->addDays($withinDays));
    }

    protected function departmentEmployees(Department $department, Request $request): View
    {
        $employees = $department->employees()
            ->with(['position'])
            ->withCount('contracts')
            ->orderBy('full_name')
            ->get();

        return view('accountant.contracts.department-employees', [
            'department' => $department,
            'employees' => $employees,
        ]);
    }

    protected function employeeContracts(int $employeeId, Request $request): View
    {
        $employee = \App\Models\Employee::with(['department', 'position'])->findOrFail($employeeId);

        $contracts = Contract::query()
            ->where('employee_id', $employee->id)
            ->with(['contractType'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        $contracts->getCollection()->transform(function (Contract $contract) {
            $contract->computed_allowance = $this->allowanceService->totalAllowance($contract);
            $contract->computed_total_income = (float) $contract->salary + $contract->computed_allowance;

            return $contract;
        });

        return view('accountant.contracts.employee-contracts', [
            'employee' => $employee,
            'department' => $employee->department,
            'contracts' => $contracts,
            'statuses' => Contract::STATUS_LABELS,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Contract $contract): View
    {
        $contract->load(['employee.department', 'employee.position', 'contractType', 'department', 'position']);

        return view('accountant.contracts.show', [
            'contract' => $contract,
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
        ]);
    }
}
