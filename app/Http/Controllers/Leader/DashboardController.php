<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Services\LeaderStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly LeaderStatsService $stats) {}

    public function index(): View
    {
        return view('leader.dashboard', $this->stats->dashboardStats(auth()->user()));
    }
}
