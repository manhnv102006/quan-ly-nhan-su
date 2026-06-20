<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractTypeRequest;
use App\Models\ContractType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContractType::query();

        if ($request->filled('search')) {
            $query->where('contract_name', 'like', '%'.$request->search.'%');
        }

        $contractTypes = $query
            ->orderBy('contract_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.contract-types.index', compact('contractTypes'));
    }

    public function create(): View
    {
        return view('admin.contract-types.create');
    }

    public function store(ContractTypeRequest $request): RedirectResponse
    {
        ContractType::create($request->validated());

        return redirect()
            ->route('admin.contract-types.index')
            ->with('success', 'Loại hợp đồng đã được tạo thành công.');
    }

    public function edit(ContractType $contractType): View
    {
        return view('admin.contract-types.edit', compact('contractType'));
    }

    public function update(ContractTypeRequest $request, ContractType $contractType): RedirectResponse
    {
        $contractType->update($request->validated());

        return redirect()
            ->route('admin.contract-types.index')
            ->with('success', 'Loại hợp đồng đã được cập nhật thành công.');
    }

    public function destroy(ContractType $contractType): RedirectResponse
    {
        $contractType->delete();

        return redirect()
            ->route('admin.contract-types.index')
            ->with('success', 'Loại hợp đồng đã được xóa mềm. Bạn có thể khôi phục sau.');
    }

    public function restore(int $id): RedirectResponse
    {
        $contractType = ContractType::onlyTrashed()->findOrFail($id);
        $contractType->restore();

        return redirect()
            ->route('admin.contract-types.index')
            ->with('success', 'Loại hợp đồng đã được khôi phục thành công.');
    }
}
