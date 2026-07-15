<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Services\AccountantStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AccountantStatsService $stats,
    ) {
    }

    public function index(): View
    {
        return view('accountant.dashboard', $this->stats->dashboardStats());
    }
}
