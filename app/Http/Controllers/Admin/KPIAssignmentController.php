<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKPIAssignmentRequest;
use App\Models\KPI;
use App\Models\KPIAssignment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KPIAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KPIAssignment::with(['kpi', 'manager', 'assignedBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('kpi', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            })
                ->orWhereHas('manager', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by KPI
        if ($request->filled('kpi_id')) {
            $query->where('kpi_id', $request->kpi_id);
        }

        // Filter by manager
        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(10);
        $kpis = KPI::where('status', 'active')->get();
        $managers = User::query()
            ->whereHas('role', fn ($q) => $q->where('name', Role::MANAGER))
            ->orderBy('name')
            ->get();

        $stats = [
            'pending' => KPIAssignment::where('status', 'pending')->count(),
            'active' => KPIAssignment::where('status', 'active')->count(),
            'completed' => KPIAssignment::where('status', 'completed')->count(),
            'total' => KPIAssignment::count(),
        ];

        return view('admin.kpi-assignments.index', compact('assignments', 'kpis', 'managers', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kpis = KPI::where('status', 'active')
            ->whereDoesntHave('assignments', function ($query) {
                $query->whereIn('status', ['pending', 'active']);
            })
            ->get();

        $managers = User::query()
            ->whereHas('role', fn ($q) => $q->where('name', Role::MANAGER))
            ->orderBy('name')
            ->get();

        return view('admin.kpi-assignments.create', compact('kpis', 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKPIAssignmentRequest $request)
    {
        $data = $request->validated();
        $data['assigned_by'] = Auth::id();
        $data['status'] = 'pending';

        KPIAssignment::create($data);

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Giao KPI thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(KPIAssignment $assignment)
    {
        $assignment->load(['kpi', 'manager', 'assignedBy']);
        return view('admin.kpi-assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KPIAssignment $assignment)
    {
        $assignment->load(['kpi', 'manager']);
        $kpis = KPI::where('status', 'active')->get();
        $managers = User::query()
            ->whereHas('role', fn ($q) => $q->where('name', Role::MANAGER))
            ->orderBy('name')
            ->get();

        // Get assigned KPI IDs for each manager (excluding current assignment)
        $assignedKpis = KPIAssignment::where('id', '!=', $assignment->id)
            ->whereIn('status', ['pending', 'active'])
            ->pluck('kpi_id')
            ->toArray();
        return view('admin.kpi-assignments.edit', compact('assignment', 'kpis', 'managers', 'assignedKpis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreKPIAssignmentRequest $request, KPIAssignment $assignment)
    {
        $assignment->update($request->validated());

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Cập nhật giao KPI thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KPIAssignment $assignment)
    {
        $assignment->delete();

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Xóa giao KPI thành công');
    }

    /**
     * Approve KPI assignment
     */
    public function approve(KPIAssignment $assignment)
    {
        $assignment->update(['status' => 'active']);

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Phê duyệt giao KPI thành công');
    }

    /**
     * Reject KPI assignment
     */
    public function reject(KPIAssignment $assignment)
    {
        $assignment->update(['status' => 'cancelled']);

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Hủy giao KPI thành công');
    }

    /**
     * Complete KPI assignment
     */
    public function complete(KPIAssignment $assignment)
    {
        $assignment->update(['status' => 'completed']);

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Đánh dấu hoàn thành thành công');
    }
}
