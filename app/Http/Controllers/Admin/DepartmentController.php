<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()
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
        $validated = $request->validate([
            'department_code' => 'required|string|max:20|unique:departments,department_code',
            'department_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive',
        ], [
            'department_code.required' => 'Mã phòng ban là bắt buộc',
            'department_code.unique' => 'Mã phòng ban đã tồn tại',
            'department_code.max' => 'Mã phòng ban không được vượt quá 20 ký tự',
            'department_name.required' => 'Tên phòng ban là bắt buộc',
            'department_name.max' => 'Tên phòng ban không được vượt quá 100 ký tự',
            'manager_id.exists' => 'Quản lý được chọn không hợp lệ',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $validated['department_code'] = strtoupper($validated['department_code']);
        $validated['manager_id'] = $validated['manager_id'] ?: null;

        Department::create($validated);

        return redirect()
            ->route('admin.departments')
            ->with('success', 'Thêm phòng ban thành công.');
    }

    public function edit(int $id): View
    {
        $department = Department::findOrFail($id);

        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        return view('admin.departments.edit', compact('department', 'employees'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'department_code' => 'required|string|max:20|unique:departments,department_code,'.$department->id,
            'department_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive',
        ], [
            'department_code.required' => 'Mã phòng ban là bắt buộc',
            'department_code.unique' => 'Mã phòng ban đã tồn tại',
            'department_code.max' => 'Mã phòng ban không được vượt quá 20 ký tự',
            'department_name.required' => 'Tên phòng ban là bắt buộc',
            'department_name.max' => 'Tên phòng ban không được vượt quá 100 ký tự',
            'manager_id.exists' => 'Quản lý được chọn không hợp lệ',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $validated['department_code'] = strtoupper($validated['department_code']);
        $validated['manager_id'] = $validated['manager_id'] ?: null;

        $department->update($validated);

        return redirect()
            ->route('admin.departments')
            ->with('success', 'Cập nhật phòng ban thành công.');
    }

    public function show(int $id): View
    {
        $department = Department::findOrFail($id);

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
