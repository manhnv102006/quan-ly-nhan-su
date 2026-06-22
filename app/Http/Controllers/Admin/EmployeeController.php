<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function create(): View
    {
        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $positions = Position::query()->orderBy('position_name')->get(['id', 'position_name']);

        return view('admin.employees.create', compact('departments', 'positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code'],
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:employees,email'],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,resigned'],
        ]);

        $validated['employee_code'] = strtoupper($validated['employee_code']);

        Employee::create($validated);

        return redirect()->route('admin.employees')->with('success', 'Thêm nhân viên thành công.');
    }

    public function show(Employee $employee): View
    {
        $employee->load(['department', 'position', 'user.role']);

        $contracts = $employee->contracts()
            ->with('contractType')
            ->latest()
            ->limit(5)
            ->get();

        $attendances = $employee->attendances()
            ->with('shift')
            ->latest('attendance_date')
            ->limit(5)
            ->get();

        $employeeKpis = $employee->employeeKpis()
            ->with('kpi')
            ->latest()
            ->limit(5)
            ->get();

        $payrolls = $employee->payrolls()
            ->with('payrollPeriod')
            ->latest()
            ->limit(5)
            ->get();

        $documents = $employee->documents()
            ->latest()
            ->get();

        return view('admin.employees.show', compact(
            'employee',
            'contracts',
            'attendances',
            'employeeKpis',
            'payrolls',
            'documents',
        ));
    }

    public function edit(Employee $employee): View
    {
        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $positions = Position::query()->orderBy('position_name')->get(['id', 'position_name']);

        return view('admin.employees.edit', compact('employee', 'departments', 'positions'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'employee_code' => ['required', 'string', 'max:20', 'unique:employees,employee_code,'.$employee->id],
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:employees,email,'.$employee->id],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,resigned'],
        ]);

        $validated['employee_code'] = strtoupper($validated['employee_code']);

        $employee->update($validated);

        return redirect()->route('admin.employees')->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            Department::where('manager_id', $employee->id)->update(['manager_id' => null]);

            $employee->delete();
        } catch (QueryException) {
            return redirect()
                ->back()
                ->with('error', 'Không thể xóa nhân viên vì còn dữ liệu liên quan trong hệ thống.');
        }

        return redirect()
            ->route('admin.employees')
            ->with('success', 'Đã xóa nhân viên thành công.');
    }
}
