<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKPIRequest;
use App\Http\Requests\UpdateKPIRequest;
use App\Models\Department;
use App\Models\KPI;
use Illuminate\Http\Request;

class KPIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KPI::with('departments');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department (nhiều - nhiều)
        if ($request->filled('department_id')) {
            $departmentId = $request->department_id;
            $query->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId);
            });
        }

        $kpis = $query->latest()->paginate(10);
        $departments = Department::all();

        return view('admin.kpis.index', compact('kpis', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();

        return view('admin.kpis.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKPIRequest $request)
    {
        $data = $request->validated();

        $departmentIds = $data['departments'];
        unset($data['departments']);

        // Giữ department_id (phòng ban chính) để tương thích ngược
        $data['department_id'] = $departmentIds[0];
        $data['max_score'] = $data['max_score'] ?? 100;

        $kpi = KPI::create($data);

        // Tự sinh mã KPI dựa trên id để đảm bảo duy nhất
        $kpi->update(['code' => 'KPI' . str_pad((string) $kpi->id, 4, '0', STR_PAD_LEFT)]);

        $kpi->departments()->sync($departmentIds);

        return redirect()
            ->route('admin.kpis.index')
            ->with('success', 'Thêm KPI thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kpi = KPI::with('departments')->findOrFail($id);
        $departments = Department::all();
        $selectedDepartments = $kpi->departments->pluck('id')->all();

        return view('admin.kpis.edit', compact('kpi', 'departments', 'selectedDepartments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKPIRequest $request, $id)
    {
        $kpi = KPI::findOrFail($id);

        $data = $request->validated();

        $departmentIds = $data['departments'];
        unset($data['departments']);

        $data['department_id'] = $departmentIds[0];
        $data['max_score'] = $data['max_score'] ?? 100;

        $kpi->update($data);
        $kpi->departments()->sync($departmentIds);

        return redirect()
            ->route('admin.kpis.index')
            ->with('success', 'Cập nhật KPI thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kpi = KPI::findOrFail($id);
        $kpi->delete();

        return redirect()
            ->route('admin.kpis.index')
            ->with('success', 'Xóa KPI thành công');
    }
}

