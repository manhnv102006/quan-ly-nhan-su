<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $overtimeRequests = OvertimeRequest::with([
            'employee.department'
        ])
        ->latest()
        ->paginate(10);

        $stats = [
            'total' => OvertimeRequest::count(),
            'pending' => OvertimeRequest::where('status','pending')->count(),
            'approved' => OvertimeRequest::where('status','approved')->count(),
            'rejected' => OvertimeRequest::where('status','rejected')->count(),
        ];

        return view(
            'admin.overtime-requests.index',
            compact(
                'overtimeRequests',
                'stats'
            )
        );
    }

    public function show(
        OvertimeRequest $overtimeRequest
    ) {
        $overtimeRequest->load([
            'employee.department',
            'employee.position'
        ]);

        return view(
            'admin.overtime-requests.show',
            compact('overtimeRequest')
        );
    }

    public function approve(
        OvertimeRequest $overtimeRequest
    ) {
        $overtimeRequest->update([
            'status' => 'approved'
        ]);

        return back()->with(
            'success',
            'Đã duyệt đơn tăng ca'
        );
    }

    public function reject(
        OvertimeRequest $overtimeRequest
    ) {
        $overtimeRequest->update([
            'status' => 'rejected'
        ]);

        return back()->with(
            'success',
            'Đã từ chối đơn tăng ca'
        );
    }
}