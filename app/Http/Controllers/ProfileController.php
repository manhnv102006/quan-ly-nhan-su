<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\AccountantNavigation;
use App\Support\EmployeeNavigation;
use App\Support\ManagerNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['role', 'employee.department', 'employee.position']);

        $layout = match (true) {
            $user->isAdmin() => 'admin',
            $user->isManager() => 'manager',
            $user->isAccountant() => 'accountant',
            default => 'employee',
        };

        return view('profile.edit', [
            'user' => $user,
            'employeeProfile' => $user->employee,
            'layout' => $layout,
            'navigation' => $this->navigationFor($user, $layout),
            'heroTheme' => match ($layout) {
                'admin' => 'from-violet-600 via-indigo-600 to-cyan-500',
                'manager' => 'from-emerald-500 via-teal-500 to-cyan-600',
                'accountant' => 'from-amber-500 via-orange-500 to-amber-600',
                default => 'from-sky-500 via-blue-500 to-indigo-600',
            },
            'dashboardRoute' => $user->dashboardRouteName(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function navigationFor(\App\Models\User $user, string $layout): array
    {
        if ($layout === 'manager') {
            return ManagerNavigation::items();
        }

        if ($layout === 'accountant') {
            return AccountantNavigation::items();
        }

        if ($layout === 'employee') {
            return EmployeeNavigation::items();
        }

        return [];
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
