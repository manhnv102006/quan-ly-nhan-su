<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesPayrollPeriods;

class PayrollPeriodController extends Controller
{
    use ManagesPayrollPeriods;

    protected function payrollPeriodRoutePrefix(): string
    {
        return 'accountant';
    }

    protected function payrollPeriodViewNamespace(): string
    {
        return 'accountant';
    }
}
