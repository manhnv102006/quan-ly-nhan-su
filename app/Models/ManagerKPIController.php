<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\KPIAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ManagerKPIController extends Controller
{
    /**
     * Display a listing of the KPI assignments for the authenticated manager.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Ensure the authenticated user is a manager.
        // This check can also be done via middleware if a dedicated 'manager' middleware exists.
        if (!$user || $user->role->name !== 'manager') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $query = KPIAssignment::with(['kpi', 'assignedBy'])
            ->where('manager_id', $user->id); // Filter by the logged-in manager's ID

        // Optional: Add search/filter functionality if needed, similar to Admin\KPIAssignmentController
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('kpi', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->orderBy('end_date', 'asc')->paginate(10);

        return view('manager.kpis.index', compact('assignments'));
    }

    /**
     * Display the specified KPI assignment. (Optional, but good for detail view)
     */
    public function show(KPIAssignment $assignment): View
    {
        $user = Auth::user();

        if (!$user || $user->role->name !== 'manager' || $assignment->manager_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem KPI này.');
        }

        $assignment->load(['kpi', 'assignedBy']);

        return view('manager.kpis.show', compact('assignment'));
    }
}