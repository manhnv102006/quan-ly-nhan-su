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

                return $department;
            });

        return view('accountant.contracts.index', [
            'departments' => $departments,
        ]);
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
