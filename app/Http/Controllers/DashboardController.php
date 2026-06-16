<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin');
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
