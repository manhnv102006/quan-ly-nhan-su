<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Models\Shift;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::latest()->paginate(10);

        return view('admin.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        return view('admin.shifts.create');
    }

    public function store(ShiftRequest $request): RedirectResponse
    {
        try {
            Shift::create($request->validated());
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()
                    ->withInput()
                    ->withErrors(['shift_name' => 'Tên ca đã tồn tại, vui lòng chọn tên khác.']);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Thêm ca làm việc thành công');
    }

    public function edit(Shift $shift): View
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(ShiftRequest $request, Shift $shift): RedirectResponse
    {
        try {
            $shift->update($request->validated());
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()
                    ->withInput()
                    ->withErrors(['shift_name' => 'Tên ca đã tồn tại, vui lòng chọn tên khác.']);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.shifts.index')
            ->with('success', 'Cập nhật ca làm việc thành công');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        try {
            $shift->delete();

            return redirect()
                ->route('admin.shifts.index')
                ->with('success', 'Xóa ca làm việc thành công');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()
                    ->route('admin.shifts.index')
                    ->with('error', 'Không thể xóa ca làm việc vì đang có nhân viên hoặc dữ liệu chấm công sử dụng ca này.');
            }

            throw $e;
        }
    }
}
