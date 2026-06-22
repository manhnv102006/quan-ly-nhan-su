<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->search;
        $status = $request->status;
        $date = $request->date;

        $attendances = Attendance::
            query()
            ->with([
                'employee.department',
                'employee.position',
                'shift'
            ])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('employee', function ($employee) use ($search) {
                    $employee->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
    
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
    
            ->when($date, function ($query) use ($date) {
                $query->whereDate('attendance_date', $date);
            })
    
            ->latest()
            ->paginate(10)
            ->withQueryString();
            
        $stats = [
            'total' => Attendance::count(),
            'present' => Attendance::where('status', 'present')->count(),
            'late' => Attendance::where('status', 'late')->count(),
            'leave' => Attendance::where('status', 'leave')->count(),
        ];

        return view(
            'admin.attendances.index',
            compact('attendances', 'stats')
        );
    }
    public function show(Attendance $attendance): View
    {
        $attendance->load([
            'employee.department',
            'employee.position',
            'shift'
        ]);

        return view(
            'admin.attendances.show',
            compact(
                'attendances',
                'stats',
                'search',
                'status',
                'date'
            )
        );
    }

}