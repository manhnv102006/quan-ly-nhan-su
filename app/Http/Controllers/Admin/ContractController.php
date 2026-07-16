<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractCancelRequest;
use App\Http\Requests\ContractConvertRequest;
use App\Http\Requests\ContractTerminateRequest;
use App\Http\Requests\ContractExtendRequest;
use App\Http\Requests\ContractStoreRequest;
use App\Http\Requests\ContractUpdateRequest;
use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\ContractAllowanceService;
use App\Services\ContractService;
use App\Services\ContractTypeConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        private readonly ContractService $service,
        private readonly ContractAllowanceService $allowanceService,
        private readonly ContractTypeConversionService $conversionService,
    ) {
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (Department $department) {
                $employeeIds = Employee::query()
                    ->where('department_id', $department->id)
                    ->pluck('id');

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

        $searchResults = collect();

        if ($search->isNotEmpty()) {
            $searchResults = Employee::query()
                ->with(['department', 'position'])
                ->withCount('contracts')
                ->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                })
                ->orderBy('full_name')
                ->limit(10)
                ->get();
        }

        return view('admin.contracts.index', [
            'departments' => $departments,
            'stats' => $this->contractStats(),
            'search' => $search->toString(),
            'searchResults' => $searchResults,
        ]);
    }

    public function departmentEmployees(Department $department, Request $request): View
    {
        $search = $request->string('search')->trim();

        $employees = Employee::query()
            ->where('department_id', $department->id)
            ->with(['position'])
            ->withCount('contracts')
            ->with(['contracts' => function ($q) {
                $q->with('contractType')
                    ->where('status', Contract::STATUS_ACTIVE)
                    ->latest('start_date')
                    ->limit(1);
            }])
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->get();

        return view('admin.contracts.department-employees', [
            'department' => $department,
            'employees' => $employees,
            'stats' => $this->contractStats(),
            'search' => $search->toString(),
        ]);
    }

    public function employeeContracts(Employee $employee, Request $request): View
    {
        $employee->load(['department', 'position']);

        $contracts = Contract::query()
            ->where('employee_id', $employee->id)
            ->with(['contractType', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('contract_type_id'), fn ($q) => $q->where('contract_type_id', $request->contract_type_id))
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.contracts.employee-contracts', [
            'employee' => $employee,
            'department' => $employee->department,
            'contracts' => $contracts,
            'stats' => $this->contractStats(),
            'statuses' => $this->statusOptions(),
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'filters' => $request->only(['status', 'contract_type_id']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.contracts.create', [
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::with(['department', 'position'])
                ->where('status', 'active')
                ->whereDoesntHave('contracts', function ($q) {
                    $q->where('status', Contract::STATUS_ACTIVE);
                })
                ->orderBy('full_name')
                ->get(),
            'departments' => Department::orderBy('department_name')->get(),
            'positions' => Position::orderBy('position_name')->get(),
            'nextCode' => $this->service->generateCode(),
            'allowanceTypes' => $this->allowanceService->activeTypes(),
            'allowanceValues' => $this->allowanceService->valuesForForm(),
            'selectedEmployeeId' => $request->integer('employee_id') ?: null,
        ]);
    }

    public function store(ContractStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()?->id;

        $contract = $this->service->create($data, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.by-employee', $contract->employee_id)
            ->with('success', 'Tạo hợp đồng thành công.');
    }

    public function show(int $id): View
    {
        $contract = Contract::withTrashed()
            ->with(['employee.department', 'employee.position', 'department', 'position', 'contractType', 'creator', 'extensions', 'terminations', 'previousContract'])
            ->findOrFail($id);

        $history = Contract::withTrashed()
            ->where('employee_id', $contract->employee_id)
            ->orderByDesc('start_date')
            ->get();

        $activityHistories = ContractHistory::query()
            ->where('employee_id', $contract->employee_id)
            ->with(['performer', 'contract', 'relatedContract'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.contracts.show', [
            'contract' => $contract,
            'history' => $history,
            'activityHistories' => $activityHistories,
            'statuses' => $this->statusOptions(),
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
        ]);
    }

    public function edit(Contract $contract): View
    {
        abort_unless($contract->isEditable(), 403, 'Chỉ cho phép sửa hợp đồng Draft hoặc Active.');

        return view('admin.contracts.edit', [
            'contract' => $contract,
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::where('status', 'active')->orderBy('full_name')->get(),
            'departments' => Department::orderBy('department_name')->get(),
            'positions' => Position::orderBy('position_name')->get(),
            'allowanceTypes' => $this->allowanceService->activeTypes(),
            'allowanceValues' => $this->allowanceService->valuesForForm($contract, $contract->position_id),
        ]);
    }

    public function update(ContractUpdateRequest $request, Contract $contract): RedirectResponse
    {
        $this->service->update($contract, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.contracts.by-employee', $contract->employee_id)
            ->with('success', 'Cập nhật hợp đồng thành công.');
    }

    public function destroy(Request $request, Contract $contract): RedirectResponse
    {
        $employeeId = $contract->employee_id;
        $this->service->softDelete($contract, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.by-employee', $employeeId)
            ->with('success', 'Đã chuyển hợp đồng vào thùng rác.');
    }

    public function trash(Request $request): View
    {
        $contracts = Contract::onlyTrashed()
            ->with(['employee', 'contractType'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim();
                $q->where('contract_code', 'like', "%{$term}%")
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where('full_name', 'like', "%{$term}%");
                    });
            })
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contracts.trash', [
            'contracts' => $contracts,
            'trashCount' => Contract::onlyTrashed()->count(),
        ]);
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $this->service->restore($contract, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.trashed')
            ->with('success', 'Khôi phục hợp đồng thành công.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $this->service->forceDelete($contract, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.trashed')
            ->with('success', 'Đã xóa vĩnh viễn hợp đồng.');
    }

    public function extendForm(Contract $contract): View
    {
        $contract->load(['employee', 'contractType']);

        abort_unless($contract->canBeExtended(), 403, 'Chỉ gia hạn được hợp đồng đang còn hiệu lực hoặc sắp hết hạn.');

        $suggestedStart = $contract->end_date
            ? $contract->end_date->copy()->addDay()->format('Y-m-d')
            : now()->format('Y-m-d');

        $suggestedEnd = $contract->end_date
            ? $contract->end_date->copy()->addDay()->addYear()->subDay()->format('Y-m-d')
            : null;

        return view('admin.contracts.extend', [
            'contract' => $contract,
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'nextCode' => $this->service->generateCode(),
            'allowanceTypes' => $this->allowanceService->activeTypes(),
            'allowanceValues' => $this->allowanceService->valuesForForm($contract, $contract->position_id),
            'positions' => Position::orderBy('position_name')->get(),
            'renewalBlocked' => $contract->isFixedTermRenewalBlocked(),
            'suggestedStart' => $suggestedStart,
            'suggestedEnd' => $suggestedEnd,
        ]);
    }

    public function extendStore(ContractExtendRequest $request, Contract $contract): RedirectResponse
    {
        if ($contract->isFixedTermRenewalBlocked()) {
            return redirect()
                ->route('admin.contracts.show', $contract)
                ->with('error', Contract::fixedTermRenewalBlockedMessage())
                ->with('suggest_convert', true);
        }

        $newContract = $this->service->extend($contract, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.contracts.show', $newContract)
            ->with('success', sprintf(
                'Gia hạn thành công. HĐ #%d → HĐ mới #%d (%s).',
                $contract->id,
                $newContract->id,
                $newContract->contract_code,
            ));
    }

    public function terminate(ContractTerminateRequest $request, Contract $contract): RedirectResponse
    {
        $this->service->terminate($contract, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Chấm dứt hợp đồng thành công.');
    }

    public function convertForm(Contract $contract): View
    {
        $contract->load(['employee', 'contractType']);

        abort_unless($contract->canBeExtended(), 403, 'Chỉ chuyển loại được hợp đồng đang còn hiệu lực hoặc sắp hết hạn.');

        $allTypes = ContractType::orderBy('contract_name')->get();
        $allowedTypes = $allTypes->filter(
            fn (ContractType $type) => $this->conversionService->isTargetAllowed($contract, $type)
        );

        return view('admin.contracts.convert', [
            'contract' => $contract,
            'contractTypes' => $allowedTypes,
            'nextCode' => $this->service->generateCode(),
            'allowanceTypes' => $this->allowanceService->activeTypes(),
            'allowanceValues' => $this->allowanceService->valuesForForm($contract, $contract->position_id),
            'positions' => Position::orderBy('position_name')->get(),
        ]);
    }

    public function convertStore(ContractConvertRequest $request, Contract $contract): RedirectResponse
    {
        $newContract = $this->service->convertType($contract, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.contracts.show', $newContract)
            ->with('success', sprintf(
                'Chuyển loại thành công. HĐ #%d → HĐ mới #%d (%s).',
                $contract->id,
                $newContract->id,
                $newContract->contract_code,
            ));
    }

    public function cancel(ContractCancelRequest $request, Contract $contract): RedirectResponse
    {
        $this->service->cancel($contract, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.contracts.by-employee', $contract->employee_id)
            ->with('success', 'Hủy hợp đồng thành công.');
    }

    public function activate(Request $request, Contract $contract): RedirectResponse
    {
        $this->service->activate($contract, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Kích hoạt hợp đồng thành công.');
    }

    public function history(Request $request): View
    {
        $histories = ContractHistory::query()
            ->with(['employee.department', 'contract', 'relatedContract', 'performer'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim();
                $q->where('summary', 'like', "%{$term}%")
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where('full_name', 'like', "%{$term}%")
                            ->orWhere('employee_code', 'like', "%{$term}%");
                    })
                    ->orWhereHas('contract', fn ($sub) => $sub->where('contract_code', 'like', "%{$term}%"));
            })
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('performed_by'), fn ($q) => $q->where('performed_by', $request->performed_by))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contracts.history', [
            'histories' => $histories,
            'employees' => Employee::orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
            'actions' => ContractHistory::ACTION_LABELS,
            'filters' => $request->only(['search', 'action', 'employee_id', 'performed_by', 'date_from', 'date_to']),
        ]);
    }

    private function statusOptions(): array
    {
        return Contract::STATUS_LABELS;
    }

    private function contractStats(): array
    {
        $today = now()->toDateString();
        $soonDate = now()->addDays(30)->toDateString();

        return [
            'total' => Contract::count(),
            'active' => Contract::where('status', Contract::STATUS_ACTIVE)->count(),
            'expired' => Contract::where('status', Contract::STATUS_EXPIRED)->count(),
            'expiring_soon' => Contract::query()
                ->where('status', Contract::STATUS_ACTIVE)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $soonDate)
                ->count(),
            'trashed' => Contract::onlyTrashed()->count(),
        ];
    }
}
