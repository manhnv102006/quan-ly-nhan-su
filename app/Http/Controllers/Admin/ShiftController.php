<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->paginate(10);

        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shift_name' => ['required', 'string'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        Shift::create($data);

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Thêm ca làm việc thành công');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'shift_name' => ['required', 'string'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        $shift->update($data);

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Cập nhật ca làm việc thành công');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Xóa ca làm việc thành công');
    }
}