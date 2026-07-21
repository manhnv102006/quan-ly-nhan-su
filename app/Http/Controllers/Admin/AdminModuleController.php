<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\View\View;


class AdminModuleController extends Controller
{
    public function positions(): View
    {
        return $this->module('Quản lý chức vụ', 'Thiết lập và quản lý các chức vụ, vị trí công việc.');
    }

    public function employees(Request $request): View
    {
        $view = $request->string('view')->value();
        $hasDepartmentParam = $request->query('department_id') !== null;

        if ($view !== 'all' && ! $hasDepartmentParam) {
            return $this->employeeDepartmentGrid();
        }

        return $this->employeeList($request);
    }

    private function employeeStats(): array
    {
        return [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'resigned' => Employee::where('status', 'resigned')->count(),
        ];
    }

    private function employeeDepartmentGrid(): View
    {
        $departments = Department::query()
            ->withCount('employees')
            ->with('manager:id,full_name')
            ->orderBy('department_name')
            ->get();

        $unassignedCount = Employee::whereNull('department_id')->count();
        $stats = $this->employeeStats();

        return view('admin.employees.departments', compact('departments', 'unassignedCount', 'stats'));
    }

    private function employeeList(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $departmentParam = $request->query('department_id');
        $unassignedOnly = $departmentParam === 'none';
        $departmentId = $unassignedOnly ? null : ((int) $departmentParam ?: null);
        $positionId = $request->integer('position_id') ?: null;
        $status = $request->string('status')->trim()->value();

        $allowedStatuses = ['active', 'inactive', 'resigned'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $employees = Employee::query()
            ->with(['department', 'position', 'user'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($unassignedOnly, fn ($query) => $query->whereNull('department_id'))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($positionId, fn ($query) => $query->where('position_id', $positionId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('employee_code')
            ->paginate(12)
            ->withQueryString();

        $stats = $this->employeeStats();

        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $positions = Position::query()->orderBy('position_name')->get(['id', 'position_name']);

        $currentDepartment = $departmentId
            ? Department::query()->withCount('employees')->find($departmentId)
            : null;

        $filters = [
            'search' => $search,
            'department_id' => $departmentId,
            'position_id' => $positionId,
            'status' => $status,
            'unassigned' => $unassignedOnly,
        ];

        return view('admin.employees.index', compact(
            'employees',
            'stats',
            'search',
            'departments',
            'positions',
            'filters',
            'currentDepartment',
            'unassignedOnly',
        ));
    }

    public function attendances(): View
    {
        return $this->module('Quản lý chấm công', 'Theo dõi giờ vào ra, ca làm việc và báo cáo chấm công.');
    }

    public function kpis(): View
    {
        return $this->module('Quản lý KPI', 'Thiết lập chỉ tiêu KPI và đánh giá hiệu suất nhân viên.');
    }

    public function payrolls(): View
    {
        return $this->module('Quản lý lương', 'Tính lương, kỳ lương và phiếu lương nhân viên.');
    }

    public function contracts(): View
    {
        return $this->module('Quản lý hợp đồng', 'Hợp đồng lao động, loại hợp đồng và thời hạn.');
    }

    public function recruitment(): View
    {
        return $this->module('Tuyển dụng', 'Tin tuyển dụng, ứng viên và lịch phỏng vấn.');
    }

    private function module(string $title, string $description): View
    {
        return view('admin.modules.placeholder', compact('title', 'description'));
    }
}
