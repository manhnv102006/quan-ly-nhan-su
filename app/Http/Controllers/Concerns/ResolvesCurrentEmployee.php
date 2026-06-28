<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

trait ResolvesCurrentEmployee
{
    protected function currentManager(): Employee
    {
        $manager = Employee::where('user_id', Auth::id())->first();
        abort_if(! $manager, 403, 'Không tìm thấy thông tin nhân viên quản lý.');

        return $manager;
    }
}
