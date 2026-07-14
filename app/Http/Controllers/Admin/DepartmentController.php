<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Services\ManagerDepartmentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly ManagerDepartmentSyncService $managerDepartmentSync,
    ) {}

    /**
     * @return array<string, mixed>
     */
    private function departmentValidationRules(?Department $department = null): array
    {
        $minMax = Department::MIN_MAX_EMPLOYEES;
        $maxMax = Department::MAX_MAX_EMPLOYEES;

        return [
            'department_code' => 'required|string|max:20|unique:departments,department_code'.($department ? ','.$department->id : ''),
            'department_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_employees' => "required|integer|min:{$minMax}|max:{$maxMax}",
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function departmentValidationMessages(): array
    {
        return [
            'department_code.required' => 'Mã phòng ban là bắt buộc',
            'department_code.unique' => 'Mã phòng ban đã tồn tại',
            'department_code.max' => 'Mã phòng ban không được vượt quá 20 ký tự',
            'department_name.required' => 'Tên phòng ban là bắt buộc',
            'department_name.max' => 'Tên phòng ban không được vượt quá 100 ký tự',
            'max_employees.required' => 'Giới hạn nhân viên là bắt buộc',
            'max_employees.integer' => 'Giới hạn nhân viên phải là số nguyên',
            'max_employees.min' => 'Giới hạn nhân viên tối thiểu là '.Department::MIN_MAX_EMPLOYEES,
            'max_employees.max' => 'Giới hạn nhân viên tối đa là '.Department::MAX_MAX_EMPLOYEES,
            'manager_id.exists' => 'Quản lý được chọn không hợp lệ',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }

    public function index(): View
    {
        $departments = Department::query()
            ->withCount('employees')
            ->orderBy('department_name')
            ->get();

        return view('admin.departments.index', [
            'title' => 'Quản lý phòng ban',
            'departments' => $departments,
        ]);
    }

    public function create(): View
    {
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('admin.departments.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->departmentValidationRules(),
            $this->departmentValidationMessages(),
        );

        $validated['department_code'] = strtoupper($validated['department_code']);
        $validated['manager_id'] = $validated['manager_id'] ?: null;
        $validated['max_employees'] = (int) $validated['max_employees'];

        Department::create($validated);

        $department = Department::query()->where('department_code', $validated['department_code'])->first();

        if ($department) {
            $this->managerDepartmentSync->syncAfterDepartmentManagerAssigned($department, $department->manager_id);
        }

        return redirect()
            ->route('admin.departments')
            ->with('success', 'Thêm phòng ban thành công.');
    }

    public function edit(int $id): View
    {
        $department = Department::withCount('employees')->findOrFail($id);

        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('admin.departments.edit', compact('department', 'employees'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $department = Department::withCount('employees')->findOrFail($id);

        $validated = $request->validate(
            $this->departmentValidationRules($department),
            $this->departmentValidationMessages(),
        );

        $currentCount = (int) $department->employees_count;

        if ((int) $validated['max_employees'] < $currentCount) {
            return back()
                ->withInput()
                ->withErrors([
                    'max_employees' => "Giới hạn không được nhỏ hơn số nhân viên hiện tại ({$currentCount}).",
                ]);
        }

        $validated['department_code'] = strtoupper($validated['department_code']);
        $validated['manager_id'] = $validated['manager_id'] ?: null;
        $validated['max_employees'] = (int) $validated['max_employees'];

        $department->update($validated);

        $this->managerDepartmentSync->syncAfterDepartmentManagerAssigned($department->fresh(), $department->manager_id);

        return redirect()
            ->route('admin.departments')
            ->with('success', 'Cập nhật phòng ban thành công.');
    }

    public function show(int $id): View
    {
        $department = Department::withCount('employees')->with([
            'employees',
            'employees.position'
        ])->findOrFail($id);
    
        return view('admin.departments.detail', compact('department'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()
            ->route('admin.departments')
            ->with('success', 'Đã xóa mềm phòng ban. Bạn có thể khôi phục từ danh sách đã xóa.');
    }

    public function trash(): View
    {
        $departments = Department::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.departments.trash', compact('departments'));
    }

    public function restore(int $id): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();

        return redirect()
            ->route('admin.departments.trash')
            ->with('success', 'Đã khôi phục phòng ban thành công.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->forceDelete();

        return redirect()
            ->route('admin.departments.trash')
            ->with('success', 'Đã xóa cứng phòng ban khỏi hệ thống.');
    }
}
