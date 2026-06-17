<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Candidate;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPost;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'stats' => [
                ['label' => 'Tài khoản', 'value' => User::count(), 'color' => 'blue', 'route' => 'admin.accounts'],
                ['label' => 'Phòng ban', 'value' => Department::count(), 'color' => 'cyan', 'route' => 'admin.departments'],
                ['label' => 'Chức vụ', 'value' => Position::count(), 'color' => 'indigo', 'route' => 'admin.positions'],
                ['label' => 'Nhân viên', 'value' => Employee::count(), 'color' => 'sky', 'route' => 'admin.employees'],
                ['label' => 'Chấm công', 'value' => Attendance::count(), 'color' => 'teal', 'route' => 'admin.attendances'],
                ['label' => 'Bảng lương', 'value' => Payroll::count(), 'color' => 'emerald', 'route' => 'admin.payrolls'],
                ['label' => 'Hợp đồng', 'value' => Contract::count(), 'color' => 'violet', 'route' => 'admin.contracts'],
                ['label' => 'Ứng viên', 'value' => Candidate::count(), 'color' => 'rose', 'route' => 'admin.recruitment'],
            ],
            'recentJobs' => JobPost::latest()->take(5)->get(),
        ]);
    }

    public function manager(): View
    {
        return view('dashboard.manager');
    }

    public function employee(): View
    {
        return view('dashboard.employee');
    }

    public function redirect(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return redirect()->route($user->dashboardRouteName());
    }
}
