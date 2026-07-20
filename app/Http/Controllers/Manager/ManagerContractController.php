<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Services\ContractAllowanceService;
use App\Services\LeaderScopeService;
use App\Services\ManagerScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ManagerContractController extends Controller
{
    public function __construct(
        private readonly ContractAllowanceService $allowanceService,
        private readonly ManagerScopeService $managerScope,
        private readonly LeaderScopeService $leaderScope,
    ) {
    }

    public function index(Request $request): View
    {
        $employeeIds = $this->scopedEmployeeIds($request);
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $expiring = $request->boolean('expiring');

        $scopedContracts = Contract::query()->whereIn('employee_id', $employeeIds);

        $expiringCount = $employeeIds === []
            ? 0
            : $this->applyExpiringScope(clone $scopedContracts)->count();

        $contracts = Contract::query()
            ->with(['employee', 'contractType'])
            ->whereIn('employee_id', $employeeIds)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('contract_code', 'like', "%{$search}%")
                        ->orWhereHas('employee', fn (Builder $employee) => $employee
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, array_keys(Contract::STATUS_LABELS), true), fn (Builder $q) => $q->where('status', $status))
            ->when($expiring, fn (Builder $q) => $this->applyExpiringScope($q))
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        return view($this->viewName($request, 'index'), [
            'contracts' => $contracts,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'expiring' => $expiring,
            ],
            'statuses' => Contract::STATUS_LABELS,
            'expiringCount' => $expiringCount,
        ]);
    }

    public function show(Request $request, Contract $contract): View
    {
        Gate::authorize('view', $contract);

        $contract->load(['employee', 'department', 'position', 'contractType']);

        $history = Contract::query()
            ->where('employee_id', $contract->employee_id)
            ->orderByDesc('start_date')
            ->get();

        return view($this->viewName($request, 'show'), [
            'contract' => $contract,
            'history' => $history,
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
        ]);
    }

    /**
     * @return list<int>
     */
    private function scopedEmployeeIds(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        if ($request->routeIs('leader.*')) {
            $leader = $this->leaderScope->resolveLeaderEmployee($user);

            return $leader ? $this->leaderScope->teamMemberIds($leader) : [];
        }

        $manager = $this->managerScope->resolveManagerEmployee($user);

        if (! $manager) {
            return [];
        }

        return $this->managerScope->managedEmployeesQuery($manager)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function applyExpiringScope(Builder $query): Builder
    {
        return $query
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>', now())
            ->whereDate('end_date', '<=', now()->addDays(30));
    }

    private function viewName(Request $request, string $page): string
    {
        $prefix = $request->routeIs('leader.*') ? 'leader' : 'manager';

        return "{$prefix}.contracts.{$page}";
    }
}
