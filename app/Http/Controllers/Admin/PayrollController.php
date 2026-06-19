<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\PayrollPeriod;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payroll::query()->with(['employee', 'payrollPeriod']);

        // Tìm kiếm theo tên nhân viên
        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo kỳ lương
        if ($request->filled('period_id')) {
            $query->where('payroll_period_id', $request->period_id);
        }

        $payrolls = $query->latest()->paginate(10)->withQueryString();
        $periods = PayrollPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        return view('admin.payrolls.index', compact('payrolls', 'periods'));
    }
}
