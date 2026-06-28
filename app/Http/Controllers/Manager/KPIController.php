<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\KPIAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KPIController extends Controller
{
    /**
     * Danh sách KPI được giao cho Manager
     */
    public function index(): View
    {
        $assignments = KPIAssignment::with([
                'kpi',
                'assignedBy',
            ])
            ->where('manager_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('manager.kpis.index', compact('assignments'));
    }

    /**
     * Chi tiết KPI
     */
    public function show(KPIAssignment $assignment): View
    {
        // Đảm bảo Manager chỉ xem KPI của chính mình
        abort_if($assignment->manager_id !== Auth::id(), 403);

        $assignment->load([
            'kpi',
            'assignedBy',
        ]);

        return view('manager.kpis.show', compact('assignment'));
    }
}