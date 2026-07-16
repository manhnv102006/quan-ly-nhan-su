@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Báo cáo nộp BH" subtitle="Theo tháng hoặc quý">
    @include('accountant.insurance.partials.sub-nav', ['active' => 'reports'])
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Báo cáo nộp bảo hiểm</h2>
                <p class="text-sm text-slate-500">{{ $label }} ({{ $start->format('d/m/Y') }} – {{ $end->format('d/m/Y') }})</p>
            </div>
            <a href="{{ route('accountant.insurance.reports.export', request()->query()) }}" class="accountant-btn-primary">Xuất Excel (CSV)</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="accountant-label">Loại báo cáo</label>
                <select name="type" class="accountant-field" onchange="this.form.submit()">
                    <option value="month" @selected($type === 'month')>Theo tháng</option>
                    <option value="quarter" @selected($type === 'quarter')>Theo quý</option>
                </select>
            </div>
            <div>
                <label class="accountant-label">Năm</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="accountant-field w-28">
            </div>
            @if($type === 'month')
                <div>
                    <label class="accountant-label">Tháng</label>
                    <select name="month" class="accountant-field">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month == $m)>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
            @else
                <div>
                    <label class="accountant-label">Quý</label>
                    <select name="quarter" class="accountant-field">
                        @for($q = 1; $q <= 4; $q++)
                            <option value="{{ $q }}" @selected($quarter == $q)>Quý {{ $q }}</option>
                        @endfor
                    </select>
                </div>
            @endif
            <button type="submit" class="accountant-btn-primary">Xem báo cáo</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Số nhân viên', 'value' => $rows->count()])
            @include('accountant.partials.stat-card', ['label' => 'Tổng NLĐ đóng', 'value' => $formatMoney($totals['employee']), 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng DN đóng', 'value' => $formatMoney($totals['employer']), 'tone' => 'text-indigo-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="bg-sky-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3 text-right">Lương đóng</th>
                            <th class="px-4 py-3 text-right">BHXH NLĐ</th>
                            <th class="px-4 py-3 text-right">BHYT NLĐ</th>
                            <th class="px-4 py-3 text-right">BHTN NLĐ</th>
                            <th class="px-4 py-3 text-right">Tổng NLĐ</th>
                            <th class="px-4 py-3 text-right">Tổng DN</th>
                            <th class="px-4 py-3">TT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            @php $p = $row['profile']; $c = $row['contrib']; @endphp
                            <tr class="hover:bg-sky-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $p->employee?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $p->employee?->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($p->contribution_salary) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($c['bhxh_employee']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($c['bhyt_employee']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($c['bhtn_employee']) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-sky-700">{{ $formatMoney($c['total_employee']) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-indigo-700">{{ $formatMoney($c['total_employer']) }}</td>
                                <td class="px-4 py-3"><span class="accountant-badge {{ $p->statusBadgeClass() }}">{{ $p->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">Không có dữ liệu trong kỳ này.</td></tr>
                        @endforelse
                    </tbody>
                    @if($rows->isNotEmpty())
                        <tfoot>
                            <tr class="bg-sky-50/80 font-bold">
                                <td class="px-4 py-3">Tổng cộng</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($totals['salary']) }}</td>
                                <td colspan="3"></td>
                                <td class="px-4 py-3 text-right text-sky-800">{{ $formatMoney($totals['employee']) }}</td>
                                <td class="px-4 py-3 text-right text-indigo-800">{{ $formatMoney($totals['employer']) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
