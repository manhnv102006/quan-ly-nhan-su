@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Ngân sách dự kiến vs thực tế" subtitle="So sánh chi phí lương">
    @include('accountant.reports.partials.sub-nav', ['active' => 'budget'])

    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">So sánh ngân sách dự kiến vs thực tế</h2>
                <p class="text-sm text-slate-500">
                    Tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                    · Dự kiến tính từ hợp đồng hiệu lực (lương + phụ cấp)
                </p>
            </div>
            <a href="{{ route('accountant.reports.budget-comparison.export', request()->query()) }}" class="accountant-btn-primary">Xuất CSV</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[120px]">
                <label class="accountant-label">Tháng</label>
                <select name="month" class="accountant-field">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month == $m)>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="accountant-label">Năm</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="accountant-field">
            </div>
            <button type="submit" class="accountant-btn-primary">Xem báo cáo</button>
        </form>

        @php
            $totals = $report['totals'];
            $variancePct = $totals['planned'] > 0
                ? round(($totals['variance'] / $totals['planned']) * 100, 1)
                : 0;
        @endphp

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Ngân sách dự kiến', 'value' => $formatMoney($totals['planned']), 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thực tế', 'value' => $formatMoney($totals['actual']), 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Chênh lệch', 'value' => $formatMoney($totals['variance']), 'tone' => $totals['variance'] > 0 ? 'text-rose-600' : 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tỷ lệ chênh', 'value' => $variancePct.'%', 'tone' => $variancePct > 0 ? 'text-rose-600' : 'text-emerald-600'])
        </div>

        @if(!$report['period'])
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Chưa có kỳ lương tháng {{ $month }}/{{ $year }} — cột thực tế = 0. Tạo và tính lương để có số liệu đối chiếu.
            </div>
        @endif

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-indigo-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết theo phòng ban</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3 text-center">NV DK</th>
                            <th class="px-4 py-3 text-center">NV TT</th>
                            <th class="px-4 py-3 text-right">Dự kiến</th>
                            <th class="px-4 py-3 text-right">Thực tế</th>
                            <th class="px-4 py-3 text-right">Chênh lệch</th>
                            <th class="px-4 py-3 text-center">Tỷ lệ</th>
                            <th class="px-4 py-3">Biểu đồ</th>
                            <th class="px-4 py-3">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['rows'] as $row)
                            @php
                                $max = max($row['planned'], $row['actual'], 1);
                                $plannedPct = round(($row['planned'] / $max) * 100);
                                $actualPct = round(($row['actual'] / $max) * 100);
                            @endphp
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['department']->department_name }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['headcount_planned'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['headcount_actual'] }}</td>
                                <td class="px-4 py-3 text-right text-indigo-700">{{ $formatMoney($row['planned']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $formatMoney($row['actual']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $row['variance'] > 0 ? 'text-rose-600' : ($row['variance'] < 0 ? 'text-sky-600' : 'text-slate-600') }}">
                                    {{ $row['variance'] > 0 ? '+' : '' }}{{ $formatMoney($row['variance']) }}
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-bold {{ $row['variance_pct'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $row['variance_pct'] > 0 ? '+' : '' }}{{ $row['variance_pct'] }}%
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-10 text-[10px] text-indigo-600">DK</span>
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-indigo-400" style="width: {{ $plannedPct }}%"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-10 text-[10px] text-emerald-600">TT</span>
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $actualPct }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($row['status'] === 'over')
                                        <span class="accountant-badge bg-rose-100 text-rose-700">Vượt NS</span>
                                    @elseif($row['status'] === 'under')
                                        <span class="accountant-badge bg-sky-100 text-sky-700">Dưới NS</span>
                                    @else
                                        <span class="accountant-badge bg-emerald-100 text-emerald-700">Đúng KH</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center text-slate-500">Không có dữ liệu ngân sách.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
