@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Xuất báo cáo tài chính NS" subtitle="Tổng hợp & xuất CSV">
<div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Báo cáo tài chính nhân sự</h2>
                <p class="text-sm text-slate-500">
                    @if($period)
                        Kỳ {{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}/{{ $period->year }}
                    @else
                        Chọn kỳ lương để xem tổng hợp
                    @endif
                </p>
            </div>
            <a href="{{ route('accountant.reports.financial.export', request()->query()) }}" class="accountant-btn-primary">Xuất báo cáo CSV</a>
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
            <button type="submit" class="accountant-btn-primary">Xem tổng hợp</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            @include('accountant.partials.stat-card', ['label' => 'Tổng lương', 'value' => $formatMoney($summary['gross_payroll']), 'tone' => 'text-amber-700'])
            @include('accountant.partials.stat-card', ['label' => 'BH NLĐ', 'value' => $formatMoney($summary['insurance']['total_employee']), 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'BH DN', 'value' => $formatMoney($summary['insurance']['total_employer']), 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thuế TNCN (ước)', 'value' => $formatMoney($summary['estimated_pit']), 'tone' => 'text-violet-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thực lĩnh (ước)', 'value' => $formatMoney($summary['net_estimate']), 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Chi phí DN', 'value' => $formatMoney($summary['employer_cost']), 'tone' => 'text-rose-600'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="accountant-card overflow-hidden">
                <div class="border-b border-indigo-100/80 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Chi phí lương theo phòng ban</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-indigo-50/60 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-4 py-3">Phòng ban</th>
                                <th class="px-4 py-3 text-right">Thực chi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($summary['salary']['rows'] as $row)
                                @if($row['total_salary'] > 0)
                                    <tr>
                                        <td class="px-4 py-3">{{ $row['department']->department_name }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $formatMoney($row['total_salary']) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($budget)
                <div class="accountant-card overflow-hidden">
                    <div class="border-b border-violet-100/80 px-5 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Ngân sách vs thực tế</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-violet-50/60 text-left text-xs font-bold uppercase text-slate-500">
                                    <th class="px-4 py-3">Phòng ban</th>
                                    <th class="px-4 py-3 text-right">DK</th>
                                    <th class="px-4 py-3 text-right">TT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($budget['rows'] as $row)
                                    <tr>
                                        <td class="px-4 py-3">{{ $row['department']->department_name }}</td>
                                        <td class="px-4 py-3 text-right text-indigo-700">{{ $formatMoney($row['planned']) }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">{{ $formatMoney($row['actual']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="accountant-card p-5">
            <h3 class="text-sm font-bold text-slate-800">Nội dung file xuất CSV</h3>
            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-600">
                <li>Tổng hợp chi phí lương, bảo hiểm, thuế TNCN (ước tính)</li>
                <li>Chi tiết chi phí lương theo từng phòng ban</li>
                <li>Bảng so sánh ngân sách dự kiến (từ HĐ) vs thực tế</li>
            </ul>
            <p class="mt-3 text-xs text-slate-500">BH và thuế là ước tính theo tỷ lệ chuẩn — đối chiếu thêm với module Bảo hiểm và Thuế TNCN.</p>
        </div>
    </div>
</x-accountant-layout>
