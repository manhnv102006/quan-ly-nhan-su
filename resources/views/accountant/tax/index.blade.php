@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
    $policy = $periodTaxPolicy ?? $currentTaxPolicy ?? null;
    $fmtM = fn ($n) => number_format((float) $n / 1_000_000, 1, ',', '.');
@endphp

<x-accountant-layout title="Thuế TNCN" subtitle="Tính thuế theo nhân viên từng kỳ lương">
<div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Tính thuế TNCN</h2>
            <p class="text-sm text-slate-500">
                @if($policy)
                    {{ $policy->name }} · GT bản thân {{ $fmtM($policy->personal_deduction) }} triệu · NPT {{ $fmtM($policy->dependent_deduction_default) }} triệu/tháng
                @else
                    Chính sách thuế theo ngày hiệu lực (cấu hình trong hệ thống)
                @endif
                · BH NLĐ chỉ tính khi nhân viên đã đăng ký bảo hiểm (trạng thái đang đóng)
                · Thuế TNCN tính theo biểu lũy tiến 5 bậc (Luật TNCN 2025, kỳ thuế 2026)
            </p>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[220px] flex-1">
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field">
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" @selected($period?->id === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Phòng ban</label>
                <select name="department_id" class="accountant-field">
                    <option value="">Tất cả phòng ban</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $dept->id)>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div class="min-w-[180px]">
                <label class="accountant-label">Lọc thuế</label>
                <select name="pit_filter" class="accountant-field">
                    <option value="">Tất cả</option>
                    <option value="with_tax" @selected(($filters['pit_filter'] ?? '') === 'with_tax')>Có thuế TNCN</option>
                    <option value="no_tax" @selected(($filters['pit_filter'] ?? '') === 'no_tax')>Không phát sinh thuế</option>
                    <option value="with_dependents" @selected(($filters['pit_filter'] ?? '') === 'with_dependents')>Có người phụ thuộc</option>
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.tax.index', array_filter(['period_id' => $period?->id])) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        @if($period)
            @if(($periodTaxPolicy ?? null)?->code === 'pit_2026')
                <div class="accountant-card p-5">
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Biểu thuế lũy tiến 5 bậc (2026)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                                    <th class="px-3 py-2">Bậc</th>
                                    <th class="px-3 py-2">Thu nhập tính thuế/tháng</th>
                                    <th class="px-3 py-2 text-right">Thuế suất</th>
                                    <th class="px-3 py-2">Công thức rút gọn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($taxBrackets2026 ?? [] as $index => $bracket)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-700">Bậc {{ $index + 1 }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $bracket['label'] }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-violet-700">{{ number_format($bracket['rate'] * 100, 0) }}%</td>
                                        <td class="px-3 py-2 text-xs text-slate-500">
                                            {{ number_format($bracket['rate'] * 100, 0) }}% × TN tính thuế
                                            @if($bracket['quick_deduction'] > 0)
                                                − {{ number_format($bracket['quick_deduction'], 0, ',', '.') }}₫
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @include('accountant.partials.stat-card', ['label' => 'Tổng thu nhập', 'value' => $formatMoney($totalGross)])
                @include('accountant.partials.stat-card', ['label' => 'Tổng thuế TNCN', 'value' => $formatMoney($totalPit), 'tone' => 'text-violet-700'])
                @include('accountant.partials.stat-card', ['label' => 'Nhân viên', 'value' => $rows->count(), 'tone' => 'text-indigo-600'])
                @include('accountant.partials.stat-card', ['label' => 'Kỳ', 'value' => $period->month.'/'.$period->year])
            </div>

            <div class="accountant-card overflow-hidden">
                <div class="border-b border-violet-100/80 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Chi tiết thuế theo nhân viên</h3>
                    <p class="text-xs text-slate-500">
                        {{ $rows->count() }} nhân viên
                        @if($hasFilters)
                            · Đang áp dụng bộ lọc
                        @endif
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-sm">
                        <thead>
                            <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-4 py-3">Nhân viên</th>
                                <th class="px-4 py-3">Phòng ban</th>
                                <th class="px-4 py-3 text-center">NPT</th>
                                <th class="px-4 py-3 text-right">Thu nhập</th>
                                <th class="px-4 py-3 text-right">BH NLĐ</th>
                                <th class="px-4 py-3 text-right">GT bản thân</th>
                                <th class="px-4 py-3 text-right">GT phụ thuộc</th>
                                <th class="px-4 py-3 text-right">TN tính thuế</th>
                                <th class="px-4 py-3 text-right">Thuế TNCN</th>
                                <th class="px-4 py-3 text-right">Thực lĩnh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rows as $row)
                                <tr class="hover:bg-violet-50/30">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $row['employee']?->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $row['employee']?->employee_code }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        {{ $row['employee']?->department?->department_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="accountant-badge bg-violet-100 text-violet-800">{{ $row['dependents_count'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ $formatMoney($row['gross']) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-500">{{ $formatMoney($row['insurance']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $formatMoney($row['personal_deduction']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $formatMoney($row['dependent_deduction']) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $formatMoney($row['taxable_income']) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-violet-700">
                                        {{ $formatMoney($row['pit']) }}
                                        @if (! empty($row['pit_breakdown']))
                                            <p class="mt-1 text-[10px] font-normal text-slate-500 leading-relaxed">
                                                @foreach ($row['pit_breakdown'] as $part)
                                                    B{{ $part['level'] }}: {{ number_format($part['amount'], 0, ',', '.') }} × {{ number_format($part['rate'] * 100, 0) }}% = {{ number_format($part['tax'], 0, ',', '.') }}₫
                                                    @if (! $loop->last)<br>@endif
                                                @endforeach
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-emerald-700">{{ $formatMoney($row['net_income']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-14 text-center text-slate-400">
                                        Không có nhân viên phù hợp bộ lọc trong kỳ này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="accountant-card p-10 text-center text-slate-500">Chưa có kỳ lương.</div>
        @endif
    </div>
</x-accountant-layout>
