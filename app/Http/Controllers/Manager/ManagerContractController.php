<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Employee;
use App\Services\ContractAllowanceService;
use App\Services\ManagerScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ManagerContractController extends Controller
{
    public function __construct(
        private readonly ManagerScopeService $managerScope,
        private readonly ContractAllowanceService $allowanceService,
    ) {
    }

    public function index(Request $request): View
    {
        $manager = $this->managerScope->resolveManagerEmployeeOrFail(Auth::user());
        if (Auth::user()->isLeader()) {
            $managedIds = Employee::query()
                ->managedByLeader($manager)
                ->pluck('id');
        } else {
            $managedIds = $this->managerScope
                ->managedEmployeesQuery($manager)
                ->pluck('id');
        }

        $contracts = Contract::query()
            ->with(['employee', 'contractType', 'department', 'position'])
            ->whereIn('employee_id', $managedIds)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim();
                $q->where(function ($sub) use ($term) {
                    $sub->where('contract_code', 'like', "%{$term}%")
                        ->orWhereHas('employee', fn($e) => $e->where('full_name', 'like', "%{$term}%"));
                });
            })
            ->when($request->boolean('expiring'), function ($q) {
                $q->where('status', Contract::STATUS_ACTIVE)
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '<=', now()->addDays(30));
            })
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        $expiringCount = Contract::query()
            ->whereIn('employee_id', $managedIds)
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();

        $view = Auth::user()->isLeader()
            ? 'leader.contracts.index'
            : 'manager.contracts.index';

        return view($view, [
            'contracts' => $contracts,
            'expiringCount' => $expiringCount,
            'filters' => $request->only(['search', 'status', 'expiring']),
            'statuses' => Contract::STATUS_LABELS,
        ]);
    }

    public function show(Contract $contract): View
    {
        Gate::authorize('view', $contract);

        $contract->load(['employee', 'department', 'position', 'contractType', 'extensions', 'terminations']);

        $history = Contract::query()
            ->where('employee_id', $contract->employee_id)
            ->orderByDesc('start_date')
            ->get();

        $view = Auth::user()->isLeader()
            ? 'leader.contracts.show'
            : 'manager.contracts.show';

        return view($view, [
            'contract' => $contract,
            'history' => $history,
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
        ]);
    }
}
