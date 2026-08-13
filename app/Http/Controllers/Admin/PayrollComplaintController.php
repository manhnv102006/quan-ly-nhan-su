<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollComplaint;
use App\Services\PayrollComplaintService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollComplaintController extends Controller
{
    public function __construct(
        private readonly PayrollComplaintService $complaints,
    ) {}

    public function index(Request $request): View
    {
        $complaints = $this->complaints
            ->filteredQuery($request->query('status'), $request->query('search'))
            ->paginate(15)
            ->withQueryString();

        $stats = $this->complaints->dashboardStats();

        return view('admin.payroll-complaints.index', compact('complaints', 'stats'));
    }

    public function show(PayrollComplaint $payrollComplaint): View
    {
        $payrollComplaint = $this->complaints->loadDetail($payrollComplaint);

        $nextPeriod = $payrollComplaint->payroll?->payrollPeriod
            ? $this->complaints->nextPeriodAfter($payrollComplaint->payroll->payrollPeriod)
            : null;

        return view('admin.payroll-complaints.show', compact('payrollComplaint', 'nextPeriod'));
    }
}
