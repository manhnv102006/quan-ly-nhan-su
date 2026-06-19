<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $positions = Position::orderBy('position_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('admin.positions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'position_name' => 'required|string|max:255|unique:positions,position_name',
            'base_salary' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'position_name.required' => 'Tên chức vụ là bắt buộc',
            'position_name.unique' => 'Tên chức vụ đã tồn tại',
            'base_salary.required' => 'Lương cơ bản là bắt buộc',
            'base_salary.numeric' => 'Lương cơ bản phải là số',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        Position::create($validated);

        return redirect()
            ->route('admin.positions')
            ->with('success', 'Thêm chức vụ thành công.');
    }

    public function show(Position $position): View
    {
        $employees = $position->employees()
            ->orderBy('full_name')
            ->get();

        return view('admin.positions.show', compact('position', 'employees'));
    }

    public function edit(Position $position): View
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'position_name' => 'required|string|max:255|unique:positions,position_name,' . $position->id,
            'base_salary' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ], [
            'position_name.required' => 'Tên chức vụ là bắt buộc',
            'position_name.unique' => 'Tên chức vụ đã tồn tại',
            'base_salary.required' => 'Lương cơ bản là bắt buộc',
            'base_salary.numeric' => 'Lương cơ bản phải là số',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        $position->update($validated);

        return redirect()
            ->route('admin.positions')
            ->with('success', 'Cập nhật chức vụ thành công.');
    }

    public function trash(Request $request): View
    {
        $positions = Position::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.positions.trash', compact('positions'));
    }
    // xóa mềm

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()
            ->route('admin.positions')
            ->with('success', 'Đã xóa mềm chức vụ thành công. Bạn có thể khôi phục từ danh sách đã xóa.');
    }
//Khôi phục
    public function restore(Request $request, int $id): RedirectResponse
    {
        $position = Position::onlyTrashed()->findOrFail($id);
        $position->restore();

        return redirect()
            ->route('admin.positions.trash')
            ->with('success', 'Đã khôi phục chức vụ thành công.');
    }
    // xóa cứng

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $position = Position::onlyTrashed()->findOrFail($id);
        $position->forceDelete();

        return redirect()
            ->route('admin.positions.trash')
            ->with('success', 'Đã xóa cứng chức vụ khỏi hệ thống.');
    }
}
