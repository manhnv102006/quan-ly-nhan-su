@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Chi phí lương theo phòng ban" subtitle="Báo cáo chi phí nhân sự">
    @include('accountant.reports.partials.sub-nav', ['active' => 'salary'])

    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Chi phí lương theo phòng ban</h2>
                <p class="text-sm text-slate-500">
                    @if($period)
                        Kỳ lương {{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}/{{ $period->year }}
                        · {{ $period->name ?? 'Kỳ '.$period->month.'/'.$period->year }}
                    @else
                        Chưa có kỳ lương cho tháng đã chọn — dữ liệu trống
                    @endif
                </p>
            </div>
            <a href="{{ route('accountant.reports.salary-by-department.export', request()->query()) }}" class="accountant-btn-primary">Xuất CSV</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[220px]">
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field" onchange="this.form.submit()">
                    <option value="">— Chọn kỳ —</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" @selected($period?->id === $p->id)>
                            {{ str_pad($p->month, 2, '0', STR_PAD_LEFT) }}/{{ $p->year }} — {{ $p->name ?? 'Kỳ lương' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="accountant-label">Tháng</label>
                <select name="month" class="accountant-field">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month == $m)>{{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="accountant-label">Năm</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="accountant-field">
            </div>
            <button type="submit" class="accountant-btn-primary">Xem báo cáo</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Tổng nhân viên', 'value' => $report['totals']['employee_count']])
            @include('accountant.partials.stat-card', ['label' => 'Lương cơ bản', 'value' => $formatMoney($report['totals']['basic_salary']), 'tone' => 'text-amber-700'])
            @include('accountant.partials.stat-card', ['label' => 'Phụ cấp', 'value' => $formatMoney($report['totals']['allowance_total']), 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thực chi', 'value' => $formatMoney($report['totals']['total_salary']), 'tone' => 'text-emerald-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-indigo-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết theo phòng ban</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="bg-indigo-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3 text-center">NV</th>
                            <th class="px-4 py-3 text-right">Lương CB</th>
                            <th class="px-4 py-3 text-right">Phụ cấp</th>
                            <th class="px-4 py-3 text-right">Tăng ca</th>
                            <th class="px-4 py-3 text-right">Thưởng</th>
                            <th class="px-4 py-3 text-right">Khấu trừ</th>
                            <th class="px-4 py-3 text-right">Thực chi</th>
                            <th class="px-4 py-3 text-right">Tỷ trọng</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['rows'] as $row)
                            @php
                                $share = $report['totals']['total_salary'] > 0
                                    ? round(($row['total_salary'] / $report['totals']['total_salary']) * 100, 1)
                                    : 0;
                            @endphp
                            <tr class="hover:bg-indigo-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $row['department']->department_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $row['department']->department_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $row['employee_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['basic_salary']) }}</td>
                                <td class="px-4 py-3 text-right text-sky-700">{{ $formatMoney($row['allowance_total']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['overtime_pay']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['bonus']) }}</td>
                                <td class="px-4 py-3 text-right text-rose-600">{{ $formatMoney($row['deduction']) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">{{ $formatMoney($row['total_salary']) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $share) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-600">{{ $share }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center text-slate-500">Không có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($report['totals']['total_salary'] > 0)
                        <tfoot class="border-t-2 border-indigo-100 bg-indigo-50/50">
                            <tr class="font-bold">
                                <td class="px-4 py-3">Tổng cộng</td>
                                <td class="px-4 py-3 text-center">{{ $report['totals']['employee_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($report['totals']['basic_salary']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($report['totals']['allowance_total']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($report['totals']['overtime_pay']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($report['totals']['bonus']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($report['totals']['deduction']) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-700">{{ $formatMoney($report['totals']['total_salary']) }}</td>
                                <td class="px-4 py-3 text-right">100%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
