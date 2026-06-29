<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeKpi;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
            ->with(['kpi', 'kpiAssignment.manager']) // Eager load để tối ưu query
            ->latest()
            ->paginate(10);

        return view('employee.kpis.index', compact('employeeKpis'));
    }


}