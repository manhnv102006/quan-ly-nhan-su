<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(Request $request): View
    {
        $leaveTypes = LeaveType::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->input('search'));
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                });
            })
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->withCount('leaveRequests')
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return view('admin.leave-types.index', [
            'leaveTypes' => $leaveTypes,
            'totalCount' => LeaveType::query()->count(),
            'activeCount' => LeaveType::query()->where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.leave-types.create');
    }

    public function store(LeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create([
            ...$request->validated(),
            'is_system' => false,
        ]);

        return redirect()
            ->route('admin.leave-types.index')
            ->with('success', 'Đã thêm loại nghỉ phép.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $data = $request->validated();

        if ($leaveType->is_system) {
            unset($data['code']);
        }

        if (! ($data['is_active'] ?? false) && in_array($leaveType->code, LeaveType::PROTECTED_ACTIVE_CODES, true)) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'Không thể tắt loại nghỉ phép này — hệ thống cần nó để tách đơn phép năm vượt số dư.']);
        }

        $leaveType->update($data);

        return redirect()
            ->route('admin.leave-types.index')
            ->with('success', 'Đã cập nhật loại nghỉ phép.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        if ($leaveType->is_system) {
            return back()->with('error', 'Không thể xóa loại nghỉ phép hệ thống. Bạn có thể tắt để ngừng sử dụng.');
        }

        if ($leaveType->leaveRequests()->exists()) {
            return back()->with('error', 'Loại nghỉ phép này đã có đơn nghỉ sử dụng. Hãy tắt thay vì xóa để giữ lịch sử.');
        }

        $leaveType->delete();

        return redirect()
            ->route('admin.leave-types.index')
            ->with('success', 'Đã xóa loại nghỉ phép.');
    }
}
