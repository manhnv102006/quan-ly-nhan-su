<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Http\Requests\StoreHolidayRequest;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $holidays = Holiday::query()
            ->orderBy('start_date', 'desc')
            ->paginate(15);
            
        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(StoreHolidayRequest $request)
    {
        Holiday::create($request->validated());

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Ngày lễ/Sự kiện đã được thêm thành công.');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(StoreHolidayRequest $request, Holiday $holiday)
    {
        $holiday->update($request->validated());

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Ngày lễ/Sự kiện đã được cập nhật thành công.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Ngày lễ/Sự kiện đã được xóa thành công.');
    }
}
