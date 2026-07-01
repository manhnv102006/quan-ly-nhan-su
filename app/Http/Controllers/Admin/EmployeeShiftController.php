<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeShiftRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeShiftController extends Controller
{
    public function index(): View
    {
        $employeeShifts = EmployeeShift::with([
            'employee.department',
            'shift',
        ])
            ->latest()
            ->paginate(10);

        return view('admin.employee-shifts.index', compact('employeeShifts'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code', 'department_id']);

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount([
                'employees as active_employees_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('department_name')
            ->get(['id', 'department_code', 'department_name']);

        $companyEmployeeCount = Employee::query()
            ->where('status', 'active')
            ->count();

        $shifts = Shift::orderBy('start_time')->get();

        return view('admin.employee-shifts.create', compact(
            'employees',
            'departments',
            'companyEmployeeCount',
            'shifts',
        ));
    }

    public function store(StoreEmployeeShiftRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $employeeIds = $this->resolveEmployeeIds($validated);

        if ($employeeIds === []) {
            return back()
                ->withInput()
                ->withErrors(['assignment_scope' => 'Không tìm thấy nhân viên phù hợp để gán ca.']);
        }

        foreach ($employeeIds as $employeeId) {
            EmployeeShift::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'work_date' => $validated['work_date'],
                ],
                [
                    'shift_id' => $validated['shift_id'],
                ],
            );
        }

        $message = match ($validated['assignment_scope']) {
            'employee' => 'Đã gán ca làm cho nhân viên.',
            'department' => 'Đã gán ca cho '.count($employeeIds).' nhân viên trong phòng ban.',
            'company' => 'Đã gán ca cho '.count($employeeIds).' nhân viên toàn công ty.',
            default => 'Đã gán ca làm.',
        };

        return redirect()
            ->route('admin.employee-shifts.index')
            ->with('success', $message);
    }

    /**
     * @return list<int>
     */
    private function resolveEmployeeIds(array $validated): array
    {
        return match ($validated['assignment_scope']) {
            'employee' => [(int) $validated['employee_id']],
            'department' => Employee::query()
                ->where('status', 'active')
                ->where('department_id', $validated['department_id'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'company' => Employee::query()
                ->where('status', 'active')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            default => [],
        };
    }
}
