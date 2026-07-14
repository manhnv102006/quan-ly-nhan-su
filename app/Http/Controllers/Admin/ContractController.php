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
        $query = Contract::with(['employee.department', 'employee.position', 'department', 'position', 'contractType', 'creator'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim();
                $q->where('contract_code', 'like', "%{$term}%")
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where('full_name', 'like', "%{$term}%")
                            ->orWhere('employee_code', 'like', "%{$term}%");
                    });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('contract_type_id'), fn($q) => $q->where('contract_type_id', $request->contract_type_id))
            ->when($request->filled('employee_id'), fn($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $deptId = $request->department_id;
                $q->where(function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId)
                        ->orWhereHas('employee', fn ($e) => $e->where('department_id', $deptId));
                });
            })
            ->when($request->filled('position_id'), function ($q) use ($request) {
                $posId = $request->position_id;
                $q->where(function ($sub) use ($posId) {
                    $sub->where('position_id', $posId)
                        ->orWhereHas('employee', fn ($e) => $e->where('position_id', $posId));
                });
            })
            ->when($request->filled('start_from'), fn ($q) => $q->whereDate('start_date', '>=', $request->start_from))
            ->when($request->filled('end_to'), fn ($q) => $q->whereDate('end_date', '<=', $request->end_to));

        $contracts = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statuses = $this->statusOptions();
        $stats = $this->contractStats();

        return view('admin.contracts.index', [
            'contracts' => $contracts,
            'statuses' => $statuses,
            'stats' => $stats,
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'departments' => Department::orderBy('department_name')->get(),
            'positions' => Position::orderBy('position_name')->get(),
            'filters' => $request->only(['search', 'status', 'contract_type_id', 'employee_id', 'department_id', 'position_id', 'start_from', 'end_to']),
        ]);
    }

    public function create(): View
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
        ]);
    }

    public function store(ContractStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()?->id;

        $this->service->create($data, $request->user()?->id);

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Tạo hợp đồng thành công.');
    }

    public function show(int $id): View
    {
        $contract = Contract::withTrashed()
            ->with(['employee.department', 'employee.position', 'department', 'position', 'contractType', 'creator', 'extensions', 'terminations', 'previousContract', 'activityLogs.performer'])
            ->findOrFail($id);

        $history = Contract::withTrashed()
            ->where('employee_id', $contract->employee_id)
            ->orderByDesc('start_date')
            ->get();

        return view('admin.contracts.show', [
            'contract' => $contract,
            'history' => $history,
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
        $this->service->update($contract, $request->validated());

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Cập nhật hợp đồng thành công.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $this->service->softDelete($contract);

        return redirect()
            ->route('admin.contracts.index')
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

    public function restore(int $id): RedirectResponse
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $this->service->restore($contract);

        return redirect()
            ->route('admin.contracts.trashed')
            ->with('success', 'Khôi phục hợp đồng thành công.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $this->service->forceDelete($contract);

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
        $this->service->terminate($contract, $request->validated());

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
        $this->service->cancel($contract, $request->validated());

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Hủy hợp đồng thành công.');
    }

    public function activate(Contract $contract): RedirectResponse
    {
        $this->service->activate($contract);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Kích hoạt hợp đồng thành công.');
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
