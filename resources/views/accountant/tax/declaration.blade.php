@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.tax.partials.sub-nav', ['active' => 'declaration'])

<x-accountant-layout title="Tờ khai thuế TNCN" subtitle="Xuất tờ khai khấu trừ thuế theo tháng/quý">
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Tờ khai thuế TNCN</h2>
                <p class="text-sm text-slate-500">{{ $label }}</p>
            </div>
            <a href="{{ route('accountant.tax.declaration.export', request()->query()) }}" class="accountant-btn-primary">Xuất tờ khai (CSV)</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="accountant-label">Loại</label>
                <select name="type" class="accountant-field">
                    <option value="month" @selected($type === 'month')>Theo tháng</option>
                    <option value="quarter" @selected($type === 'quarter')>Theo quý</option>
                </select>
            </div>
            <div>
                <label class="accountant-label">Năm</label>
                <input type="number" name="year" value="{{ $year }}" class="accountant-field w-28">
            </div>
            @if($type === 'month')
                <div>
                    <label class="accountant-label">Tháng</label>
                    <select name="month" class="accountant-field">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month == $m)>T{{ $m }}</option>
                        @endfor
                    </select>
                </div>
            @else
                <div>
                    <label class="accountant-label">Quý</label>
                    <select name="quarter" class="accountant-field">
                        @for($q = 1; $q <= 4; $q++)
                            <option value="{{ $q }}" @selected($quarter == $q)>Q{{ $q }}</option>
                        @endfor
                    </select>
                </div>
            @endif
            <button type="submit" class="accountant-btn-primary">Xem tờ khai</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Nhân viên', 'value' => $totals['employees']])
            @include('accountant.partials.stat-card', ['label' => 'Tổng thu nhập', 'value' => $formatMoney($totals['gross'])])
            @include('accountant.partials.stat-card', ['label' => 'Tổng thuế khấu trừ', 'value' => $formatMoney($totals['pit']), 'tone' => 'text-violet-700'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">NV</th>
                            <th class="px-4 py-3">Kỳ</th>
                            <th class="px-4 py-3 text-right">Thu nhập</th>
                            <th class="px-4 py-3 text-center">NPT</th>
                            <th class="px-4 py-3 text-right">TN tính thuế</th>
                            <th class="px-4 py-3 text-right">Thuế TNCN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3 font-medium">{{ $row['employee']?->full_name }}</td>
                                <td class="px-4 py-3 text-xs">{{ $row['period']?->name }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['gross']) }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['dependents_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($row['taxable_income']) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-violet-700">{{ $formatMoney($row['pit']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Không có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
