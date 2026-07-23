@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Bảng lương - {{ $department->department_name }}" subtitle="Phiếu lương nhân viên">
<div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.payrolls.slips', array_filter(['period_id' => request('period_id')])) }}" class="text-amber-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $department->department_code }} · {{ $stats['count'] }} phiếu lương</p>
            </div>
            <a href="{{ route('accountant.payrolls.slips', array_filter(['period_id' => request('period_id')])) }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <form method="GET" class="accountant-card grid grid-cols-1 gap-4 p-5 md:grid-cols-4">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div>
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div>
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field">
                    <option value="">Tất cả kỳ</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>
                            {{ $period->name ?? ('Tháng '.$period->month.'/'.$period->year) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="submit" class="accountant-btn-primary">Lọc</button>
                @if(request()->anyFilled(['search', 'period_id']))
                    <a href="{{ route('accountant.payrolls.slips', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">Xóa lọc</a>
                @endif
            </div>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Phiếu lương', 'value' => $stats['count']])
            @include('accountant.partials.stat-card', ['label' => 'Lương CB', 'value' => $formatMoney($stats['basic_salary']), 'tone' => 'text-slate-700'])
            @include('accountant.partials.stat-card', ['label' => 'Khấu trừ', 'value' => $formatMoney($stats['deduction']), 'tone' => 'text-rose-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thực lĩnh', 'value' => $formatMoney($stats['total_salary']), 'tone' => 'text-amber-700'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Bảng lương nhân viên</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Kỳ lương</th>
                            <th class="px-5 py-3 text-right">Lương CB</th>
                            <th class="px-5 py-3 text-right">Phụ cấp</th>
                            <th class="px-5 py-3 text-right">Thưởng</th>
                            <th class="px-5 py-3 text-right">Khấu trừ</th>
                            <th class="px-5 py-3 text-right">Thực lĩnh</th>
                            <th class="px-5 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3 text-center">Xuất</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payrolls as $payroll)
                            @php
                                $allowanceTotal = (float) $payroll->allowance
                                    + (float) $payroll->allowance_meal
                                    + (float) $payroll->allowance_phone
                                    + (float) $payroll->allowance_fuel
                                    + (float) $payroll->allowance_position;
                            @endphp
                            <tr class="hover:bg-amber-50/40">
                                <td class="px-5 py-3">
                                    <p class="font-semibold">{{ $payroll->employee?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $payroll->employee?->employee_code }}</p>
                                </td>
                                <td class="px-5 py-3">{{ $payroll->payrollPeriod?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ $formatMoney($payroll->basic_salary) }}</td>
                                <td class="px-5 py-3 text-right">{{ $formatMoney($allowanceTotal) }}</td>
                                <td class="px-5 py-3 text-right">{{ $formatMoney($payroll->bonus) }}</td>
                                <td class="px-5 py-3 text-right text-rose-600">-{{ $formatMoney($payroll->deduction) }}</td>
                                <td class="px-5 py-3 text-right font-bold text-amber-800">{{ $formatMoney($payroll->total_salary) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="accountant-badge bg-amber-100 text-amber-800">{{ $payroll->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ route('accountant.payrolls.pdf', $payroll) }}" class="accountant-btn-secondary !px-2 !py-1 !text-xs">PDF</a>
                                        <a href="{{ route('accountant.payrolls.excel', $payroll) }}" class="accountant-btn-secondary !px-2 !py-1 !text-xs">Excel</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-slate-500">
                                    Phòng ban chưa có phiếu lương phù hợp.
                                    @if(!request('period_id'))
                                        <span class="block mt-1 text-xs">Thử chọn kỳ lương hoặc tính lương tại Quản lý kỳ lương.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($payrolls->isNotEmpty())
                        <tfoot class="border-t-2 border-amber-100 bg-amber-50/50">
                            <tr class="font-bold">
                                <td colspan="6" class="px-5 py-3 text-right text-xs uppercase text-slate-500">Tổng phòng ban</td>
                                <td class="px-5 py-3 text-right text-amber-800">{{ $formatMoney($stats['total_salary']) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            @if($payrolls->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $payrolls->links() }}</div>
            @endif
        </div>
    </div>
</x-accountant-layout>
