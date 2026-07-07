<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeKPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeKPIController extends Controller
{
    use AuthorizesRequests;

    /**
     * Hiển thị danh sách các mục tiêu KPI được giao cho nhân viên đang đăng nhập.
     */
    public function index(): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employee = $user->employee;

        // Nếu user chưa được liên kết với hồ sơ nhân viên, trả về mảng rỗng.
        if (!$employee) {
            $employeeKpis = collect()->paginate(10);
            return view('employee.kpis.index', compact('employeeKpis'));
        }

        // Sử dụng relationship để lấy KPI, đảm bảo chỉ lấy của nhân viên đang đăng nhập
        $employeeKpis = $employee->employeeKpis()
            ->with(['kpi.tasks', 'kpiAssignment.manager']) // Eager load để tối ưu query
            ->latest()
            ->paginate(10);

        return view('employee.kpis.index', compact('employeeKpis'));
    }

    public function edit(EmployeeKPI $employeeKpi): View
    {
        EmployeeKPI::markOverdueAsNotCompleted();
        $employeeKpi->refresh();

        $this->ensureEmployeeOwnsKpi($employeeKpi);

        $employeeKpi->load(['kpi', 'kpiAssignment.manager']);
        $statusOptions = $this->statusOptions();

        return view('employee.kpis.edit', compact('employeeKpi', 'statusOptions'));
    }

    public function update(Request $request, EmployeeKPI $employeeKpi): RedirectResponse
    {
        EmployeeKPI::markOverdueAsNotCompleted();
        $employeeKpi->refresh();

        $this->ensureEmployeeOwnsKpi($employeeKpi);

        if ($employeeKpi->status === EmployeeKPI::STATUS_NOT_COMPLETED) {
            return redirect()
                ->route('employee.kpis.index')
                ->with('error', 'KPI này đã quá hạn và được chuyển sang trạng thái không hoàn thành.');
        }

        $validated = $request->validate([
            'progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys($this->statusOptions()))],
        ]);

        $employeeKpi->update($validated);

        return redirect()
            ->route('employee.kpis.index')
            ->with('success', 'Cập nhật tiến độ KPI thành công.');
    }

    private function ensureEmployeeOwnsKpi(EmployeeKPI $employeeKpi): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employee = $user->employee;

        abort_unless($employee && $employeeKpi->employee_id === $employee->id, 403);
    }

    private function statusOptions(): array
    {
        return [
            EmployeeKPI::STATUS_PENDING => 'Chờ bắt đầu',
            EmployeeKPI::STATUS_IN_PROGRESS => 'Đang thực hiện',
            EmployeeKPI::STATUS_COMPLETED => 'Hoàn thành',
        ];
    }

}
