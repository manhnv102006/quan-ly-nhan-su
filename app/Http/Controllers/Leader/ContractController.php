<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractAllowanceService;
use App\Services\LeaderScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        private readonly LeaderScopeService $scope,
        private readonly ContractAllowanceService $allowanceService,
    ) {
    }

    public function index(Request $request): View
    {
        $leader = $this->scope->resolveLeaderEmployeeOrFail($request->user());
        $teamIds = $this->scope->teamMemberIds($leader);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->query('status'),
            'expiring' => $request->boolean('expiring'),
        ];

        $query = Contract::query()
            ->with(['employee', 'contractType'])
            ->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('contract_code', 'like', "%{$search}%")
                        ->orWhereHas('employee', fn ($employee) => $employee
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array($filters['status'], array_keys(Contract::STATUS_LABELS), true),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when($filters['expiring'], fn ($q) => $q->where('status', Contract::STATUS_ACTIVE)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>', now())
                ->whereDate('end_date', '<=', now()->addDays(30)))
            ->orderByDesc('start_date');

        $contracts = $query->paginate(15)->withQueryString();

        $expiringCount = Contract::query()
            ->when($teamIds !== [], fn ($q) => $q->whereIn('employee_id', $teamIds), fn ($q) => $q->whereRaw('0 = 1'))
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();

        return view('leader.contracts.index', [
            'contracts' => $contracts,
            'expiringCount' => $expiringCount,
            'filters' => $filters,
            'statuses' => Contract::STATUS_LABELS,
        ]);
    }

    public function show(Request $request, Contract $contract): View
    {
        Gate::authorize('view', $contract);

        $contract->load(['employee', 'department', 'position', 'contractType']);

        $history = Contract::query()
            ->where('employee_id', $contract->employee_id)
            ->whereKeyNot($contract->id)
            ->orderByDesc('start_date')
            ->get(['id', 'contract_code', 'start_date', 'end_date', 'status']);

        return view('leader.contracts.show', [
            'contract' => $contract,
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
            'history' => $history,
        ]);
    }
}
