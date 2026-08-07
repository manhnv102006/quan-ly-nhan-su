<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeShiftRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use App\Services\EmployeeShiftAssignmentService;
use App\Services\EmployeeShiftScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeShiftController extends Controller
{
    public function __construct(
        private readonly EmployeeShiftAssignmentService $assignmentService,
        private readonly EmployeeShiftScheduleService $scheduleService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'employee_id' => $request->integer('employee_id') ?: null,
            'work_month' => $request->input('work_month', now()->format('Y-m')),
            'shift_id' => $request->integer('shift_id') ?: null,
        ];

        $employeeShifts = $this->scheduleService
            ->filteredQuery($filters['employee_id'], $filters['work_month'], $filters['shift_id'])
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $monthSummary = $this->scheduleService->summarizeMonth(
            $filters['work_month'],
            $filters['employee_id'],
            $filters['shift_id'],
        );

        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $shifts = Shift::orderBy('start_time')->get(['id', 'shift_name', 'start_time', 'end_time']);

        return view('admin.employee-shifts.index', compact(
            'employeeShifts',
            'filters',
            'monthSummary',
            'employees',
            'shifts',
        ));
    }

    public function create(Request $request): View
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

        $selectedShiftId = (int) old('shift_id', $request->integer('shift_id'));
        $selectedShift = $selectedShiftId > 0
            ? Shift::query()->find($selectedShiftId)
            : null;

        return view('admin.employee-shifts.create', compact(
            'employees',
            'departments',
            'companyEmployeeCount',
            'shifts',
            'selectedShift',
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

        $conflicts = $this->assignmentService->findOverlappingConflicts($validated, $employeeIds);

        if ($conflicts !== []) {
            return back()
                ->withInput()
                ->withErrors(['shift_id' => $conflicts]);
        }

        $assignedCount = $this->assignmentService->assign($validated, $employeeIds);
        $dayCount = $this->assignmentService->countDates($validated);
        $employeeCount = count($employeeIds);

        $scopeLabel = match ($validated['assignment_scope']) {
            'employee' => '1 nhân viên',
            'department' => $employeeCount.' nhân viên trong phòng ban',
            'company' => $employeeCount.' nhân viên toàn công ty',
            default => $employeeCount.' nhân viên',
        };

        $periodLabel = match ($validated['period_mode']) {
            'single' => '1 ngày',
            'month' => 'cả tháng ('.$dayCount.' ngày)',
            'year' => 'cả năm ('.$dayCount.' ngày)',
            'range' => $dayCount.' ngày',
            'mon_wed_fri' => 'thứ 2, 4, 6 trong tháng ('.$dayCount.' ngày)',
            'tue_thu_sat' => 'thứ 3, 5, 7 trong tháng ('.$dayCount.' ngày)',
            default => $dayCount.' ngày',
        };

        return redirect()
            ->route('admin.employee-shifts.index')
            ->with('success', "Đã gán ca cho {$scopeLabel} trong {$periodLabel} ({$assignedCount} lịch ca).");
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
