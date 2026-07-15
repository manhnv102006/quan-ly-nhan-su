@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.tax.partials.sub-nav', ['active' => 'settlement'])

<x-accountant-layout title="Quyết toán thuế TNCN" subtitle="Quyết toán cuối năm theo nhân viên">
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Quyết toán thuế TNCN {{ $year }}</h2>
                <p class="text-sm text-slate-500">So sánh thuế đã khấu trừ vs thuế phải nộp cả năm</p>
            </div>
            <a href="{{ route('accountant.tax.settlement.export', ['year' => $year]) }}" class="accountant-btn-primary">Xuất quyết toán (CSV)</a>
        </div>

        <form method="GET" class="accountant-card flex items-end gap-4 p-5">
            <div>
                <label class="accountant-label">Năm quyết toán</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="accountant-field w-32">
            </div>
            <button type="submit" class="accountant-btn-primary">Xem</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Đã khấu trừ', 'value' => $formatMoney($totals['withheld']), 'tone' => 'text-violet-700'])
            @include('accountant.partials.stat-card', ['label' => 'Phải nộp/năm', 'value' => $formatMoney($totals['liability']), 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Hoàn thuế', 'value' => $formatMoney($totals['refund']), 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Nộp thêm', 'value' => $formatMoney($totals['pay_more']), 'tone' => 'text-rose-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead>
                        <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3 text-center">Tháng</th>
                            <th class="px-4 py-3 text-right">Tổng thu nhập</th>
                            <th class="px-4 py-3 text-right">TN tính thuế/năm</th>
                            <th class="px-4 py-3 text-right">Đã khấu trừ</th>
                            <th class="px-4 py-3 text-right">Phải nộp/năm</th>
                            <th class="px-4 py-3 text-right">Chênh lệch</th>
                            <th class="px-4 py-3 text-center">Kết quả</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            @php
                                $badge = match($row['settlement_status']) {
                                    'refund' => 'bg-emerald-100 text-emerald-800',
                                    'pay_more' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                                $statusLabel = match($row['settlement_status']) {
                                    'refund' => 'Hoàn thuế',
                                    'pay_more' => 'Nộp thêm',
                                    default => 'Đủ thuế',
                                };
                            @endphp
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $row['employee']?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $row['employee']?->taxProfile?->tax_code ?: '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $row['months_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['total_gross']) }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['taxable_annual']) }}</td>
                                <td class="px-4 py-3 text-right text-violet-700">{{ $formatMoney($row['pit_withheld']) }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ $formatMoney($row['pit_liability']) }}</td>
                                <td class="px-4 py-3 text-right {{ $row['difference'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $formatMoney(abs($row['difference'])) }}
                                    {{ $row['difference'] >= 0 ? '(+)' : '(-)' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="accountant-badge {{ $badge }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">Chưa có dữ liệu lương năm {{ $year }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
