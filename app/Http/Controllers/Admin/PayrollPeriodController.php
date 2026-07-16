<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesPayrollPeriods;

class PayrollPeriodController extends Controller
{
    use ManagesPayrollPeriods;

    protected function payrollPeriodRoutePrefix(): string
    {
        return 'admin';
    }

    protected function payrollPeriodViewNamespace(): string
    {
        return 'admin';
    }
}
