<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Models\ContractExtension;
use App\Models\ContractTermination;
use App\Models\ContractType;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contract::with(['employee', 'contractType']);

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($query) use ($request) {
                $query->where('full_name', 'like', '%'.$request->search.'%')
                    ->orWhere('employee_code', 'like', '%'.$request->search.'%');
            })->orWhere('contract_code', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract_type_id')) {
            $query->where('contract_type_id', $request->contract_type_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $contracts = $query
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        $statuses = [
            'active' => 'Đang hiệu lực',
            'expired' => 'Đã hết hạn',
            'terminated' => 'Đã thanh lý',
        ];

        return view('admin.contracts.index', [
            'contracts' => $contracts,
            'statuses' => $statuses,
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.contracts.create', [
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'statuses' => [
                'active' => 'Đang hiệu lực',
                'expired' => 'Đã hết hạn',
                'terminated' => 'Đã thanh lý',
            ],
        ]);
    }

    public function store(ContractRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        Contract::create($data);

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được tạo thành công.');
    }

    public function show(Contract $contract): View
    {
        return view('admin.contracts.show', [
            'contract' => $contract->load(['employee', 'contractType', 'extensions', 'terminations']),
        ]);
    }

    public function edit(Contract $contract): View
    {
        return view('admin.contracts.edit', [
            'contract' => $contract,
            'contractTypes' => ContractType::orderBy('contract_name')->get(),
            'employees' => Employee::orderBy('full_name')->get(),
            'statuses' => [
                'active' => 'Đang hiệu lực',
                'expired' => 'Đã hết hạn',
                'terminated' => 'Đã thanh lý',
            ],
        ]);
    }

    public function update(ContractRequest $request, Contract $contract): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($contract->file_path) {
                Storage::disk('public')->delete($contract->file_path);
            }
            $data['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $contract->update($data);

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được cập nhật thành công.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được xóa mềm. Bạn có thể khôi phục sau.');
    }

    public function restore(int $id): RedirectResponse
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $contract->restore();

        return redirect()
            ->route('admin.contracts.index')
            ->with('success', 'Hợp đồng đã được khôi phục thành công.');
    }

    public function extend(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate([
            'new_end_date' => ['required', 'date', 'after_or_equal:'.$contract->end_date?->toDateString() ?: $contract->start_date->toDateString()],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'new_end_date.required' => 'Ngày gia hạn là bắt buộc.',
            'new_end_date.date' => 'Ngày gia hạn không hợp lệ.',
            'new_end_date.after_or_equal' => 'Ngày gia hạn phải bằng hoặc sau ngày kết thúc hiện tại.',
        ]);

        ContractExtension::create([
            'contract_id' => $contract->id,
            'old_end_date' => $contract->end_date,
            'new_end_date' => $request->new_end_date,
            'note' => $request->note,
        ]);

        $contract->update([
            'end_date' => $request->new_end_date,
            'status' => $contract->status === 'terminated' ? 'terminated' : 'active',
        ]);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Hợp đồng đã được gia hạn thành công.');
    }

    public function terminate(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate([
            'end_date' => ['required', 'date', 'after_or_equal:'.$contract->start_date->toDateString()],
            'note' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'end_date.required' => 'Ngày thanh lý là bắt buộc.',
            'end_date.date' => 'Ngày thanh lý không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày thanh lý phải bằng hoặc sau ngày bắt đầu.',
            'file.mimes' => 'Biên bản thanh lý phải là PDF, DOC hoặc DOCX.',
            'file.max' => 'Biên bản thanh lý không được vượt quá 10MB.',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('contract-terminations', 'public');
        }

        ContractTermination::create([
            'contract_id' => $contract->id,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'file_path' => $filePath,
        ]);

        $contract->update([
            'end_date' => $request->end_date,
            'status' => 'terminated',
        ]);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Hợp đồng đã được thanh lý thành công.');
    }
}
