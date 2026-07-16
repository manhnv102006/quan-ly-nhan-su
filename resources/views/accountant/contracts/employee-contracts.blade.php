@php
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
@endphp

<x-accountant-layout title="Hợp đồng - {{ $employee->full_name }}" subtitle="Danh sách hợp đồng nhân viên">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.contracts.index') }}" class="text-amber-700 hover:underline">Phòng ban</a>
                    @if($department)
                        <span>/</span>
                        <a href="{{ route('accountant.contracts.index', ['department_id' => $department->id]) }}" class="text-amber-700 hover:underline">{{ $department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-700">{{ $employee->full_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                </p>
            </div>
            @if($department)
                <a href="{{ route('accountant.contracts.index', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">← Nhân viên</a>
            @endif
        </div>

        <form method="GET" action="{{ route('accountant.contracts.index') }}" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Trạng thái</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.contracts.index', ['employee_id' => $employee->id]) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Hợp đồng của nhân viên</h3>
                <p class="text-xs text-slate-500">{{ $contracts->total() }} hợp đồng</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead>
                        <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Mã HĐ</th>
                            <th class="px-5 py-3">Loại HĐ</th>
                            <th class="px-5 py-3 text-right">Lương</th>
                            <th class="px-5 py-3 text-right">Phụ cấp</th>
                            <th class="px-5 py-3 text-right">Tổng TN</th>
                            <th class="px-5 py-3">Thời hạn</th>
                            <th class="px-5 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-amber-50/40">
                                <td class="px-5 py-4">
                                    <a href="{{ route('accountant.contracts.show', $contract) }}"
                                       class="font-bold text-amber-800 hover:underline">
                                        {{ $contract->contract_code }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">{{ $contract->contractType?->contract_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-right font-bold text-slate-800">{{ $formatMoney($contract->salary) }}</td>
                                <td class="px-5 py-4 text-right text-amber-700">{{ $formatMoney($contract->computed_allowance ?? 0) }}</td>
                                <td class="px-5 py-4 text-right font-bold text-slate-900">{{ $formatMoney($contract->computed_total_income ?? $contract->salary) }}</td>
                                <td class="px-5 py-4 text-xs">
                                    {{ optional($contract->start_date)->format('d/m/Y') ?? '—' }}
                                    →
                                    {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}
                                    @if($contract->isExpiringSoon())
                                        <span class="mt-1 block text-[11px] font-bold text-rose-600">Sắp hết hạn</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('accountant.contracts.show', $contract) }}" class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center text-slate-500">Nhân viên chưa có hợp đồng.</td>
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
