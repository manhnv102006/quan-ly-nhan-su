<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllowanceTypeRequest;
use App\Models\AllowanceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllowanceTypeController extends Controller
{
    public function index(Request $request): View
    {
        $allowanceTypes = AllowanceType::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim();
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            })
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.allowance-types.index', compact('allowanceTypes'));
    }

    public function create(): View
    {
        return view('admin.allowance-types.create');
    }

    public function store(AllowanceTypeRequest $request): RedirectResponse
    {
        AllowanceType::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
            'is_system' => false,
        ]);

        return redirect()
            ->route('admin.allowance-types.index')
            ->with('success', 'Đã thêm loại phụ cấp.');
    }

    public function edit(AllowanceType $allowanceType): View
    {
        return view('admin.allowance-types.edit', compact('allowanceType'));
    }

    public function update(AllowanceTypeRequest $request, AllowanceType $allowanceType): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($allowanceType->is_system) {
            unset($data['code']);
        }

        $allowanceType->update($data);

        return redirect()
            ->route('admin.allowance-types.index')
            ->with('success', 'Đã cập nhật loại phụ cấp.');
    }

    public function destroy(AllowanceType $allowanceType): RedirectResponse
    {
        if ($allowanceType->is_system) {
            return back()->with('error', 'Không thể xóa loại phụ cấp hệ thống.');
        }

        if ($allowanceType->contractAllowances()->exists()) {
            return back()->with('error', 'Loại phụ cấp đang được dùng trong hợp đồng.');
        }

        $allowanceType->delete();

        return redirect()
            ->route('admin.allowance-types.index')
            ->with('success', 'Đã xóa loại phụ cấp.');
    }
}
