<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OvertimeController extends Controller
{
    public function index(): View
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();

        $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
            ->latest('overtime_date')
            ->paginate(10);

        return view('employee.overtime.index', compact('overtimeRequests'));
    }
}