<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $attendances = Attendance::query()
            ->with([
                'employee.department',
                'employee.position',
                'shift'
            ])
            ->latest()
            ->paginate(10);

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
            compact('attendance')
        );
    }

}