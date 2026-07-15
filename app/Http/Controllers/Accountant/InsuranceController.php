<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
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
        $query = EmployeeInsurance::query()->with(['employee.department', 'manager']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $profiles = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $stats = [
            'active' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_ACTIVE)->count(),
            'suspended' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_SUSPENDED)->count(),
            'stopped' => EmployeeInsurance::where('status', EmployeeInsurance::STATUS_STOPPED)->count(),
            'no_profile' => Employee::where('status', 'active')->whereDoesntHave('insurance')->count(),
        ];

        $resignedAlerts = $this->insurance->resignedWithActiveInsurance();

        return view('accountant.insurance.index', compact('profiles', 'stats', 'resignedAlerts'));
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->whereDoesntHave('insurance')
            ->orderBy('full_name')
            ->get();

        $rates = $this->insurance->defaultRates();

        return view('accountant.insurance.create', compact('employees', 'rates'));
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
            ->route('accountant.insurance.index')
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
            ->route('accountant.insurance.index')
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
            ->route('accountant.insurance.index')
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
        $rules = [
            'employee_id' => 'required|exists:employees,id|unique:employee_insurances,employee_id'.($ignoreId ? ",{$ignoreId}" : ''),
            'social_insurance_number' => 'nullable|string|max:50',
            'health_insurance_code' => 'nullable|string|max:50',
            'contribution_salary' => 'required|numeric|min:0',
            'bhxh_employee_rate' => 'required|numeric|min:0|max:100',
            'bhxh_employer_rate' => 'required|numeric|min:0|max:100',
            'bhyt_employee_rate' => 'required|numeric|min:0|max:100',
            'bhyt_employer_rate' => 'required|numeric|min:0|max:100',
            'bhtn_employee_rate' => 'required|numeric|min:0|max:100',
            'bhtn_employer_rate' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,suspended,stopped',
            'note' => 'nullable|string|max:2000',
        ];

        if ($ignoreId) {
            unset($rules['employee_id']);
        }

        return $request->validate($rules, [
            'employee_id.unique' => 'Nhân viên này đã có hồ sơ bảo hiểm.',
            'contribution_salary.required' => 'Mức lương đóng BH là bắt buộc.',
        ]);
    }

    /**
     * Form nhập %, lưu DB dạng thập phân.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeRates(array $validated): array
    {
        foreach ([
            'bhxh_employee_rate', 'bhxh_employer_rate',
            'bhyt_employee_rate', 'bhyt_employer_rate',
            'bhtn_employee_rate', 'bhtn_employer_rate',
        ] as $field) {
            if (isset($validated[$field]) && (float) $validated[$field] > 1) {
                $validated[$field] = round((float) $validated[$field] / 100, 4);
            }
        }

        return $validated;
    }
}
