<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Employee;
use App\Services\ContractAllowanceService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeContractController extends Controller
{
    public function __construct(private readonly ContractAllowanceService $allowanceService)
    {
    }

    private function getEmployee(): Employee
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (! $employee) {
            abort(403, 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên.');
        }

        return $employee;
    }

    public function index(): View
    {
        $employee = $this->getEmployee();

        $contracts = Contract::query()
            ->with(['contractType', 'department', 'position'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('start_date')
            ->paginate(10);

        $activeContract = $contracts->first(fn (Contract $c) => $c->status === Contract::STATUS_ACTIVE);

        return view('employee.contracts.index', compact('employee', 'contracts', 'activeContract'));
    }

    public function show(Contract $contract): View
    {
        Gate::authorize('view', $contract);

        $contract->load(['employee', 'department', 'position', 'contractType', 'extensions', 'terminations']);

        return view('employee.contracts.show', [
            'contract' => $contract,
            'allowanceBreakdown' => $this->allowanceService->breakdown($contract),
            'totalAllowance' => $this->allowanceService->totalAllowance($contract),
        ]);
    }

    public function download(Contract $contract): StreamedResponse|Response
    {
        Gate::authorize('download', $contract);

        if (! $contract->file_path || ! Storage::disk('public')->exists($contract->file_path)) {
            abort(404, 'Không tìm thấy file hợp đồng.');
        }

        return Storage::disk('public')->download($contract->file_path, basename($contract->file_path));
    }
}
