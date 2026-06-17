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

    public function trash(Request $request): View
    {
        $positions = Position::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.positions.trash', compact('positions'));
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()
            ->route('admin.positions')
            ->with('success', 'Đã xóa mềm chức vụ thành công. Bạn có thể khôi phục từ danh sách đã xóa.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $position = Position::onlyTrashed()->findOrFail($id);
        $position->restore();

        return redirect()
            ->route('admin.positions.trash')
            ->with('success', 'Đã khôi phục chức vụ thành công.');
    }
}
