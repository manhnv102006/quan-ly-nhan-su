<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\Request;

class EmployeeShiftController extends Controller
{
    public function index()
    {
        $employeeShifts = EmployeeShift::with([
            'employee',
            'shift'
        ])
        ->latest()
        ->paginate(10);

        return view(
            'admin.employee-shifts.index',
            compact('employeeShifts')
        );
    }

    public function create()
    {
        $employees = Employee::orderBy('full_name')->get();

        $shifts = Shift::orderBy('start_time')->get();

        return view(
            'admin.employee-shifts.create',
            compact(
                'employees',
                'shifts'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'required|date',
        ]);

        EmployeeShift::create($request->all());

        return redirect()
            ->route('admin.employee-shifts.index')
            ->with(
                'success',
                'Đã gán ca làm.'
            );
    }
}