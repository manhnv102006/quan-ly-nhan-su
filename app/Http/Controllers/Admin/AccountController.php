<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        return view('admin.accounts.index', compact('users', 'stats'));
    }
}
