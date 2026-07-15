@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.payrolls.partials.sub-nav', ['active' => 'slips'])

<x-accountant-layout title="Bảng lương" subtitle="Xem và xuất phiếu lương nhân viên">
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Bảng lương nhân viên</h2>
                <p class="text-sm text-slate-500">Lọc theo kỳ lương hoặc tên nhân viên</p>
            </div>
            <a href="{{ route('accountant.payroll-periods.index') }}" class="accountant-btn-primary">Quản lý kỳ lương</a>
        </div>

        <form method="GET" class="accountant-card grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            <div>
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div>
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field">
                    <option value="">Tất cả kỳ</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="accountant-btn-primary flex-1">Lọc</button>
                @if(request()->anyFilled(['search', 'period_id']))
                    <a href="{{ route('accountant.payrolls.slips') }}" class="accountant-btn-secondary">Xóa</a>
                @endif
            </div>
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
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
                            <tr class="hover:bg-amber-50/40">
                                <td class="px-5 py-3">
                                    <p class="font-semibold">{{ $payroll->employee?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $payroll->employee?->employee_code }}</p>
                                </td>
                                <td class="px-5 py-3">{{ $payroll->payrollPeriod?->name }}</td>
                                <td class="px-5 py-3 text-right">{{ $formatMoney($payroll->basic_salary) }}</td>
                                <td class="px-5 py-3 text-right">{{ $formatMoney($payroll->allowance) }}</td>
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
                            <tr><td colspan="9" class="px-5 py-12 text-center text-slate-500">Không có phiếu lương phù hợp.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payrolls->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $payrolls->links() }}</div>
            @endif
        </div>
    </div>
</x-accountant-layout>
