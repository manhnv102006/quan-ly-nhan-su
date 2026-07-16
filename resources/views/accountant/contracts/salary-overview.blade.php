@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Lương & phụ cấp HĐ" subtitle="Hợp đồng hiệu lực — chỉ xem">
    @include('accountant.contracts.partials.sub-nav', ['active' => 'salary'])

    <div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Bảng lương & phụ cấp theo hợp đồng</h2>
            <p class="text-sm text-slate-500">Danh sách hợp đồng đang hiệu lực — dùng cho tính lương, bảo hiểm, thuế.</p>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã HĐ, tên NV..." class="accountant-field">
            </div>
            <div class="min-w-[180px]">
                <label class="accountant-label">Phòng ban</label>
                <select name="department_id" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                            {{ $department->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('accountant.contracts.salary-overview') }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Hợp đồng hiệu lực</h3>
                <p class="text-xs text-slate-500">{{ $contracts->total() }} hợp đồng</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Mã HĐ</th>
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3">Loại HĐ</th>
                            <th class="px-4 py-3 text-right">Lương CB</th>
                            <th class="px-4 py-3 text-right">Phụ cấp</th>
                            <th class="px-4 py-3 text-right">Tổng thu nhập</th>
                            <th class="px-4 py-3">Hết hạn</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-amber-50/40">
                                <td class="px-4 py-3 font-bold text-amber-800">{{ $contract->contract_code }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold">{{ $contract->employee->full_name ?? '—' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $contract->employee->employee_code ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $contract->employee->department->department_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $contract->contractType->contract_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $formatMoney($contract->salary) }}</td>
                                <td class="px-4 py-3 text-right text-amber-700">{{ $formatMoney($contract->computed_allowance) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900">{{ $formatMoney($contract->computed_total_income) }}</td>
                                <td class="px-4 py-3 text-xs">
                                    {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không HH' }}
                                    @if($contract->isExpiringSoon())
                                        <span class="mt-1 block text-[11px] font-bold text-rose-600">Sắp hết hạn</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('accountant.contracts.show', $contract) }}" class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center text-slate-500">Không có hợp đồng hiệu lực phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-accountant-layout>
