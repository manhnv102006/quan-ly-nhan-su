<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\KPI;
use Illuminate\Http\Request;

class KPIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kpis = KPI::with('department')->paginate(10);
        return view('admin.kpis.index', compact('kpis'));
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
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'weight' => 'required|numeric|min:1|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        KPI::create($request->all());

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
        $kpi = KPI::findOrFail($id);
        $departments = Department::all();

        return view('admin.kpis.edit', compact('kpi', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'weight' => 'required|numeric|min:1|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        $kpi = KPI::findOrFail($id);

        $kpi->update($request->all());

        return redirect()
            ->route('admin.kpis.index')
            ->with('success', 'Cập nhật KPI thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
  
}
