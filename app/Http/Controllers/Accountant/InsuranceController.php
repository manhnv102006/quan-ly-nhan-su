<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeInsurance;
use App\Services\InsuranceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InsuranceController extends Controller
{
    public function __construct(
        private readonly InsuranceService $insurance,
    ) {}

    public function index(Request $request): View
    {
        if ($request->filled('employee_id')) {
            return $this->employeeInsurance($request->integer('employee_id'));
        }

        if ($request->filled('department_id')) {
            return $this->departmentEmployees(Department::findOrFail($request->department_id), $request);
        }

        $departments = Department::query()
            ->where('status', 'active')
            ->withCount('employees')
            ->orderBy('department_name')
            ->get()
            ->map(function (Department $department) {
                $employeeIds = $department->employees()->pluck('id');

                $department->insurance_profiles_count = $employeeIds->isEmpty()
                    ? 0
                    : EmployeeInsurance::query()->whereIn('employee_id', $employeeIds)->count();

                $department->active_insurance_count = $employeeIds->isEmpty()
                    ? 0
                    : EmployeeInsurance::query()
                        ->whereIn('employee_id', $employeeIds)
                        ->where('status', EmployeeInsurance::STATUS_ACTIVE)
                        ->count();

                $department->no_insurance_count = Employee::query()
                    ->where('department_id', $department->id)
                    ->where('status', 'active')
                    ->whereDoesntHave('insurance')
                    ->count();

                return $department;
            });

        $stats = [
            'active' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_ACTIVE)->count(),
            'suspended' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_SUSPENDED)->count(),
            'stopped' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_STOPPED)->count(),
            'no_profile' => Employee::where('status', 'active')->whereDoesntHave('insurance')->count(),
        ];

        $resignedAlerts = $this->insurance->resignedWithActiveInsurance();

        return view('accountant.insurance.index', compact('departments', 'stats', 'resignedAlerts'));
    }

    protected function departmentEmployees(Department $department, Request $request): View
    {
        $allEmployees = $department->employees()
            ->with('insurance')
            ->orderBy('full_name')
            ->get();

        $departmentStats = [
            'total' => $allEmployees->count(),
            'active' => $allEmployees->filter(fn ($e) => $e->insurance?->status === EmployeeInsurance::STATUS_ACTIVE)->count(),
            'no_profile' => $allEmployees->filter(fn ($e) => ! $e->insurance)->count(),
        ];

        $query = $department->employees()
            ->with(['position', 'insurance'])
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('insurance_status')) {
            match ($request->insurance_status) {
                'active' => $query->whereHas('insurance', fn ($q) => $q->where('status', EmployeeInsurance::STATUS_ACTIVE)),
                'suspended' => $query->whereHas('insurance', fn ($q) => $q->where('status', EmployeeInsurance::STATUS_SUSPENDED)),
                'stopped' => $query->whereHas('insurance', fn ($q) => $q->where('status', EmployeeInsurance::STATUS_STOPPED)),
                'none' => $query->whereDoesntHave('insurance'),
                default => null,
            };
        }

        $employees = $query->get()->map(function (Employee $employee) {
            if ($employee->insurance) {
                $employee->insurance_contributions = $this->insurance->calculateContributions($employee->insurance);
            }

            return $employee;
        });

        return view('accountant.insurance.department-employees', [
            'department' => $department,
            'employees' => $employees,
            'departmentStats' => $departmentStats,
            'filters' => $request->only(['search', 'insurance_status']),
        ]);
    }

    protected function employeeInsurance(int $employeeId): View
    {
        $employee = Employee::with(['department', 'position', 'insurance.manager'])->findOrFail($employeeId);
        $insurance = $employee->insurance;
        $contributions = $insurance ? $this->insurance->calculateContributions($insurance) : null;

        return view('accountant.insurance.employee', compact('employee', 'insurance', 'contributions'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $selectedEmployee = null;

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::with('department')->findOrFail($request->employee_id);

            if ($selectedEmployee->insurance) {
                return redirect()
                    ->route('accountant.insurance.index', ['employee_id' => $selectedEmployee->id])
                    ->with('info', 'Nhân viên đã có hồ sơ bảo hiểm.');
            }
        }

        $employees = Employee::query()
            ->where('status', 'active')
            ->whereDoesntHave('insurance')
            ->orderBy('full_name')
            ->get();

        $rates = $this->insurance->defaultRates();

        return view('accountant.insurance.create', compact('employees', 'rates', 'selectedEmployee'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProfile($request);
        $validated = $this->normalizeRates($validated);

        $employee = Employee::findOrFail($validated['employee_id']);
        if ($employee->insurance) {
            return back()->with('error', 'Nhân viên đã có hồ sơ bảo hiểm.');
        }

        EmployeeInsurance::create(array_merge($validated, [
            'managed_by' => auth()->id(),
            'status' => EmployeeInsurance::STATUS_ACTIVE,
        ]));

        return redirect()
            ->route('accountant.insurance.index', ['employee_id' => $employee->id])
            ->with('success', 'Đã thêm hồ sơ tham gia bảo hiểm.');
    }

    public function edit(EmployeeInsurance $insurance): View
    {
        $insurance->load(['employee.department', 'manager']);
        $contributions = $this->insurance->calculateContributions($insurance);

        return view('accountant.insurance.edit', compact('insurance', 'contributions'));
    }

    public function update(Request $request, EmployeeInsurance $insurance): RedirectResponse
    {
        $validated = $this->validateProfile($request, $insurance->id);
        $validated = $this->normalizeRates($validated);

        unset($validated['employee_id']);

        $insurance->update(array_merge($validated, [
            'managed_by' => auth()->id(),
        ]));

        return redirect()
            ->route('accountant.insurance.index', ['employee_id' => $insurance->employee_id])
            ->with('success', 'Đã cập nhật hồ sơ bảo hiểm.');
    }

    public function stop(Request $request, EmployeeInsurance $insurance): RedirectResponse
    {
        $validated = $request->validate([
            'end_date' => 'required|date',
            'stop_reason' => 'required|string|max:500',
        ]);

        $insurance->update([
            'status' => EmployeeInsurance::STATUS_STOPPED,
            'end_date' => $validated['end_date'],
            'stop_reason' => $validated['stop_reason'],
            'managed_by' => auth()->id(),
        ]);

        return redirect()
            ->route('accountant.insurance.index', ['employee_id' => $insurance->employee_id])
            ->with('success', 'Đã ngừng đóng bảo hiểm cho nhân viên.');
    }

    public function stopResigned(Employee $employee): RedirectResponse
    {
        $profile = $employee->insurance;

        if (! $profile || $profile->status !== EmployeeInsurance::STATUS_ACTIVE) {
            return back()->with('error', 'Không có hồ sơ BH đang đóng để ngừng.');
        }

        $this->insurance->stopForResignation($profile, 'Nhân viên nghỉ việc - ngừng đóng BH');

        return back()->with('success', "Đã ngừng đóng BH cho {$employee->full_name}.");
    }

    public function reports(Request $request): View
    {
        $type = $request->input('type', 'month');
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $quarter = (int) $request->input('quarter', (int) ceil(now()->month / 3));

        [$start, $end, $label] = $this->periodRange($type, $year, $month, $quarter);

        $profiles = $this->insurance->profilesForPeriod($start, $end);
        $totals = ['employee' => 0, 'employer' => 0, 'salary' => 0];

        $rows = $profiles->map(function (EmployeeInsurance $profile) use (&$totals) {
            $c = $this->insurance->calculateContributions($profile);
            $totals['employee'] += $c['total_employee'];
            $totals['employer'] += $c['total_employer'];
            $totals['salary'] += (float) $profile->contribution_salary;

            return ['profile' => $profile, 'contrib' => $c];
        });

        return view('accountant.insurance.reports', compact(
            'type', 'year', 'month', 'quarter', 'label', 'rows', 'totals', 'start', 'end'
        ));
    }

    public function exportReport(Request $request): Response
    {
        $type = $request->input('type', 'month');
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $quarter = (int) $request->input('quarter', (int) ceil(now()->month / 3));

        [$start, $end, $label] = $this->periodRange($type, $year, $month, $quarter);

        $profiles = $this->insurance->profilesForPeriod($start, $end);
        $report = $this->insurance->buildReportRows($profiles);
        $csv = $this->insurance->toCsv($report);

        $filename = 'bao_cao_bh_'.str_replace([' ', '/'], '_', $label).'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function suggestSalary(Employee $employee): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'salary' => $this->insurance->suggestContributionSalary($employee),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function periodRange(string $type, int $year, int $month, int $quarter): array
    {
        if ($type === 'quarter') {
            $quarter = max(1, min(4, $quarter));
            $startMonth = ($quarter - 1) * 3 + 1;
            $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();
            $label = "Quý {$quarter}/{$year}";

            return [$start, $end, $label];
        }

        $start = Carbon::create($year, max(1, min(12, $month)), 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $label = "Tháng {$month}/{$year}";

        return [$start, $end, $label];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProfile(Request $request, ?int $ignoreId = null): array
    {
        $limits = EmployeeInsurance::rateLimitsPercent();

        $rules = [
            'employee_id' => 'required|exists:employees,id|unique:employee_insurances,employee_id'.($ignoreId ? ",{$ignoreId}" : ''),
            'social_insurance_number' => 'nullable|string|max:50',
            'health_insurance_code' => 'nullable|string|max:50',
            'contribution_salary' => 'required|numeric|min:0',
            'bhxh_employee_rate' => 'required|numeric|min:0|max:'.$limits['bhxh_employee_rate']['max'],
            'bhxh_employer_rate' => 'required|numeric|min:0|max:'.$limits['bhxh_employer_rate']['max'],
            'bhyt_employee_rate' => 'required|numeric|min:0|max:'.$limits['bhyt_employee_rate']['max'],
            'bhyt_employer_rate' => 'required|numeric|min:0|max:'.$limits['bhyt_employer_rate']['max'],
            'bhtn_rate' => 'required|numeric|min:0|max:'.$limits['bhtn_rate']['max'],
            'start_date' => 'required|date',
            'note' => 'nullable|string|max:2000',
        ];

        if ($ignoreId) {
            unset($rules['employee_id']);
            $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
            $rules['status'] = 'required|in:active,suspended,stopped';
        }

        $messages = [
            'employee_id.unique' => 'Nhân viên này đã có hồ sơ bảo hiểm.',
            'contribution_salary.required' => 'Mức lương đóng BH là bắt buộc.',
        ];

        foreach ($limits as $field => $config) {
            $messages["{$field}.max"] = "{$config['label']} không được vượt quá {$config['max']}%.";
            $messages["{$field}.required"] = "Tỷ lệ {$config['label']} là bắt buộc.";
        }

        return $request->validate($rules, $messages);
    }

    /**
     * Form luôn nhập tỷ lệ theo % (vd: 1 = 1%, 8 = 8%) — chuyển sang thập phân lưu DB.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeRates(array $validated): array
    {
        foreach ([
            'bhxh_employee_rate', 'bhxh_employer_rate',
            'bhyt_employee_rate', 'bhyt_employer_rate',
            'bhtn_rate',
        ] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = round((float) $validated[$field] / 100, 4);
            }
        }

        if (isset($validated['bhtn_rate'])) {
            $validated['bhtn_employee_rate'] = $validated['bhtn_rate'];
            $validated['bhtn_employer_rate'] = $validated['bhtn_rate'];
            unset($validated['bhtn_rate']);
        }

        return $validated;
    }
}
