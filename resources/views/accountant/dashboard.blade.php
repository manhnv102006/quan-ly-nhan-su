@php
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
@endphp
@inject('statsService', 'App\Services\AccountantStatsService')

@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $formatShort = function ($n) {
        $n = (float) $n;
        if ($n >= 1_000_000_000) {
            return round($n / 1_000_000_000, 1).' tỷ';
        }
        if ($n >= 1_000_000) {
            return round($n / 1_000_000, 1).' tr';
        }

        return number_format($n, 0, ',', '.');
    };

    $ps = $payrollStats;
    $fin = $financial;
    $trendMax = max(1, (float) $monthlyTrend->max('total'));
    $quarterMax = max(1, (float) $quarterlySummary['months']->max('total'));
    $periodLabel = $currentPeriod
        ? 'Tháng '.str_pad((string) $currentPeriod->month, 2, '0', STR_PAD_LEFT).'/'.$currentPeriod->year
        : 'Chưa có kỳ lương';
@endphp

<x-accountant-layout title="Dashboard Kế toán" subtitle="Tổng quan lương · Chi phí NS · Hợp đồng sắp hết hạn">
    <div class="accountant-page">
        {{-- Hero --}}
        <section class="accountant-hero">
            <div class="absolute -right-20 top-0 h-64 w-64 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-300"></span>
                        Dashboard tài chính nhân sự
                    </span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Xin chào {{ $firstName }}, tổng quan lương toàn công ty tháng {{ now()->format('m/Y') }}.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-amber-100/90">
                        Theo dõi thống kê lương, chi phí nhân sự theo tháng/quý và cảnh báo hợp đồng sắp hết hạn ảnh hưởng đến mức lương.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('accountant.payroll-periods.index') }}" class="accountant-btn-primary !shadow-black/10">Quản lý kỳ lương</a>
                        <a href="{{ route('accountant.reports.financial') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                            Báo cáo tài chính
                        </a>
                        <a href="{{ route('accountant.contracts.expiring') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                            HĐ sắp hết hạn
                            @if($expiring['stats']['within_30'] > 0)
                                <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold">{{ $expiring['stats']['within_30'] }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:w-[420px]">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-200">Kỳ đang xem</p>
                        <p class="mt-2 text-lg font-bold">{{ $currentPeriod?->name ?? 'Chưa tạo kỳ' }}</p>
                        <p class="mt-1 text-sm text-amber-100/85">
                            <span class="accountant-badge {{ $statsService->periodStatusBadge($currentPeriod?->status) }}">
                                {{ $statsService->periodStatusLabel($currentPeriod?->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-200">Chi phí DN (ước)</p>
                        <p class="mt-2 text-lg font-bold">{{ $formatShort($fin['employer_cost'] ?? 0) }}</p>
                        <p class="mt-1 text-sm text-amber-100/85">{{ $activeEmployees }} NV · {{ $departmentCount }} phòng ban</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bộ lọc tháng / quý --}}
        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[100px]">
                <label class="accountant-label">Năm</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="accountant-field">
            </div>
            <div class="min-w-[120px]">
                <label class="accountant-label">Tháng</label>
                <select name="month" class="accountant-field">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month === $m)>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="accountant-label">Quý</label>
                <select name="quarter" class="accountant-field">
                    @for($q = 1; $q <= 4; $q++)
                        <option value="{{ $q }}" @selected($quarter === $q)>Quý {{ $q }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="accountant-label">Xem chi phí</label>
                <select name="cost_view" class="accountant-field">
                    <option value="month" @selected($costView === 'month')>Theo tháng</option>
                    <option value="quarter" @selected($costView === 'quarter')>Theo quý</option>
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Áp dụng</button>
            @unless($selectedPeriodExists)
                <p class="w-full text-xs text-amber-700">Chưa có kỳ lương tháng {{ $month }}/{{ $year }} — đang hiển thị kỳ gần nhất.</p>
            @endunless
        </form>

        {{-- Thống kê lương toàn công ty --}}
        <div>
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Thống kê lương toàn công ty</h3>
                    <p class="text-sm text-slate-500">{{ $periodLabel }} · {{ $ps['employee_count'] }} nhân viên có bảng lương</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-6">
                @include('accountant.partials.stat-card', ['label' => 'Tổng thu nhập', 'value' => $formatMoney($ps['gross_income']), 'note' => 'Lương + PC + TC + thưởng', 'tone' => 'text-amber-700'])
                @include('accountant.partials.stat-card', ['label' => 'Thực lĩnh', 'value' => $formatMoney($ps['net_payroll']), 'note' => 'Sau khấu trừ', 'tone' => 'text-emerald-600'])
                @include('accountant.partials.stat-card', ['label' => 'Lương cơ bản', 'value' => $formatShort($ps['basic_salary']), 'note' => 'Tổng CB', 'tone' => 'text-slate-800'])
                @include('accountant.partials.stat-card', ['label' => 'Phụ cấp', 'value' => $formatShort($ps['allowance_total']), 'note' => 'Tất cả loại PC', 'tone' => 'text-sky-600'])
                @include('accountant.partials.stat-card', ['label' => 'Tăng ca', 'value' => $formatShort($ps['overtime_pay']), 'note' => 'OT pay', 'tone' => 'text-violet-600'])
                @include('accountant.partials.stat-card', ['label' => 'TB thực lĩnh/NV', 'value' => $formatMoney($ps['avg_net']), 'note' => 'Trung bình', 'tone' => 'text-indigo-600'])
            </div>
        </div>

        {{-- Chi phí NS + biểu đồ --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <div class="accountant-card p-5 xl:col-span-3">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">
                            @if($costView === 'quarter')
                                Tổng chi phí nhân sự — {{ $quarterlySummary['label'] }}
                            @else
                                Xu hướng chi phí lương (6 tháng)
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500">Tổng thực lĩnh theo kỳ lương</p>
                    </div>
                    @if($costView === 'quarter')
                        <div class="text-right">
                            <p class="text-2xl font-extrabold text-amber-700">{{ $formatMoney($quarterlySummary['total']) }}</p>
                            <p class="text-xs text-slate-400">TB {{ $formatMoney($quarterlySummary['avg_monthly']) }}/tháng</p>
                        </div>
                    @endif
                </div>

                @if($costView === 'quarter')
                    <div class="flex h-52 items-end gap-4 border-b border-slate-100 pb-2">
                        @foreach($quarterlySummary['months'] as $item)
                            @php $height = max(8, ($item['total'] / $quarterMax) * 100); @endphp
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-[10px] font-bold text-amber-700">{{ $formatShort($item['total']) }}</span>
                                <div class="w-full max-w-[72px] rounded-t-xl bg-gradient-to-t from-amber-500 to-orange-400 shadow-md shadow-amber-200/50 transition-all" style="height: {{ $height }}%"></div>
                                <span class="text-xs font-semibold text-slate-600">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-center text-xs">
                        <div class="rounded-xl bg-amber-50 px-3 py-2">
                            <p class="text-slate-400">Lương thực chi</p>
                            <p class="font-bold text-amber-800">{{ $formatShort($quarterlySummary['total']) }}</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 px-3 py-2">
                            <p class="text-slate-400">BH DN (ước)</p>
                            <p class="font-bold text-indigo-800">{{ $formatShort($fin['insurance']['total_employer'] ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 px-3 py-2">
                            <p class="text-slate-400">Tổng chi phí DN</p>
                            <p class="font-bold text-rose-800">{{ $formatShort($fin['employer_cost'] ?? 0) }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex h-52 items-end gap-2 border-b border-slate-100 pb-2 sm:gap-3">
                        @foreach($monthlyTrend as $item)
                            @php $height = max(8, ($item['total'] / $trendMax) * 100); @endphp
                            <div class="group flex flex-1 flex-col items-center gap-2">
                                <span class="text-[10px] font-bold text-amber-700 opacity-0 transition group-hover:opacity-100">{{ $formatShort($item['total']) }}</span>
                                <div class="w-full rounded-t-lg bg-gradient-to-t from-indigo-500 to-violet-400 shadow-sm transition-all group-hover:from-amber-500 group-hover:to-orange-400" style="height: {{ $height }}%"></div>
                                <span class="text-[10px] font-semibold text-slate-500 sm:text-xs">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($monthlyTrend->isEmpty())
                        <p class="py-10 text-center text-sm text-slate-400">Chưa có dữ liệu kỳ lương.</p>
                    @endif
                @endif
            </div>

            <div class="accountant-card p-5 xl:col-span-2">
                <h3 class="text-sm font-bold text-slate-800">Chi phí nhân sự chi tiết</h3>
                <p class="mb-4 text-xs text-slate-500">{{ $periodLabel }}</p>
                <dl class="space-y-3">
                    @foreach([
                        ['Lương thực chi', $fin['gross_payroll'] ?? 0, 'text-amber-700', 'bg-amber-50'],
                        ['BH người lao động', $fin['insurance']['total_employee'] ?? 0, 'text-sky-700', 'bg-sky-50'],
                        ['BH doanh nghiệp', $fin['insurance']['total_employer'] ?? 0, 'text-indigo-700', 'bg-indigo-50'],
                        ['Thuế TNCN (ước)', $fin['estimated_pit'] ?? 0, 'text-violet-700', 'bg-violet-50'],
                        ['Thực lĩnh (ước)', $fin['net_estimate'] ?? 0, 'text-emerald-700', 'bg-emerald-50'],
                    ] as [$label, $amount, $tone, $bg])
                        <div class="flex items-center justify-between rounded-xl {{ $bg }} px-4 py-3">
                            <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                            <dd class="text-sm font-bold {{ $tone }}">{{ $formatMoney($amount) }}</dd>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between rounded-xl border-2 border-rose-200 bg-rose-50 px-4 py-3">
                        <dt class="text-xs font-bold text-rose-800">Tổng chi phí DN</dt>
                        <dd class="text-base font-extrabold text-rose-700">{{ $formatMoney($fin['employer_cost'] ?? 0) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Lương theo phòng ban --}}
            <div class="accountant-card overflow-hidden xl:col-span-2">
                <div class="flex items-center justify-between border-b border-amber-50 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Lương theo phòng ban</h3>
                        <p class="text-xs text-slate-500">Phân bổ chi phí thực lĩnh {{ $periodLabel }}</p>
                    </div>
                    <a href="{{ route('accountant.reports.salary-by-department', ['year' => $year, 'month' => $month]) }}" class="text-xs font-semibold text-amber-700 hover:underline">Xem báo cáo →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="bg-amber-50/60 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-5 py-3">Phòng ban</th>
                                <th class="px-5 py-3 text-center">NV</th>
                                <th class="px-5 py-3 text-right">Thực chi</th>
                                <th class="px-5 py-3">Tỷ trọng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($departmentBreakdown as $row)
                                <tr class="hover:bg-amber-50/30">
                                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $row['department']->department_name }}</td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ $row['employee_count'] }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-emerald-700">{{ $formatMoney($row['total_salary']) }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500" style="width: {{ min(100, $row['share']) }}%"></div>
                                            </div>
                                            <span class="w-10 text-right text-xs font-bold text-slate-500">{{ $row['share'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Chưa có dữ liệu lương theo phòng ban.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- HĐ sắp hết hạn --}}
            <div class="accountant-card overflow-hidden">
                <div class="border-b border-rose-50 bg-gradient-to-r from-rose-50/80 to-orange-50/50 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Hợp đồng sắp hết hạn</h3>
                            <p class="text-xs text-slate-500">Ảnh hưởng trực tiếp đến mức lương</p>
                        </div>
                        <a href="{{ route('accountant.contracts.expiring') }}" class="text-xs font-semibold text-rose-700 hover:underline">Tất cả →</a>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach([
                            ['7 ngày', $expiring['stats']['within_7'], 'text-rose-700 bg-rose-100'],
                            ['15 ngày', $expiring['stats']['within_15'], 'text-orange-700 bg-orange-100'],
                            ['30 ngày', $expiring['stats']['within_30'], 'text-amber-700 bg-amber-100'],
                            ['60 ngày', $expiring['stats']['within_60'], 'text-slate-600 bg-slate-100'],
                        ] as [$lbl, $cnt, $cls])
                            <div class="rounded-xl {{ $cls }} px-2 py-2 text-center">
                                <p class="text-lg font-extrabold">{{ $cnt }}</p>
                                <p class="text-[9px] font-bold uppercase">{{ $lbl }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($expiring['upcoming'] as $row)
                        @php
                            $contract = $row['contract'];
                            $urgencyClass = match($row['urgency']) {
                                'critical' => 'border-l-4 border-l-rose-500 bg-rose-50/40',
                                'warning' => 'border-l-4 border-l-orange-400 bg-orange-50/30',
                                default => 'border-l-4 border-l-amber-300',
                            };
                        @endphp
                        <li class="px-5 py-3.5 {{ $urgencyClass }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-800">{{ $contract->employee?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $contract->employee?->department?->department_name ?? '—' }} · {{ $contract->contract_code }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $row['days_left'] <= 7 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $row['days_left'] }} ngày
                                </span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Hết hạn {{ $contract->end_date?->format('d/m/Y') }}</span>
                                <span class="font-bold text-emerald-700">{{ $formatMoney($row['total_income']) }}/tháng</span>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-slate-400">Không có HĐ sắp hết hạn trong 30 ngày.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Hàng dưới: kỳ lương + việc cần xử lý + truy cập nhanh --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="accountant-card overflow-hidden lg:col-span-2">
                <div class="border-b border-amber-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Kỳ lương gần đây</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-5 py-3">Kỳ</th>
                                <th class="px-5 py-3">Trạng thái</th>
                                <th class="px-5 py-3 text-right">Tổng chi</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentPeriods as $period)
                                <tr class="hover:bg-amber-50/30">
                                    <td class="px-5 py-3 font-semibold">{{ $period->name ?? 'T'.$period->month.'/'.$period->year }}</td>
                                    <td class="px-5 py-3">
                                        <span class="accountant-badge {{ $statsService->periodStatusBadge($period->status) }}">
                                            {{ $statsService->periodStatusLabel($period->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-bold text-emerald-700">{{ $formatMoney($period->payrolls_sum_total_salary ?? 0) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('accountant.payroll-periods.show', $period) }}" class="text-xs font-semibold text-amber-700 hover:underline">Chi tiết</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Chưa có kỳ lương.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="accountant-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Việc cần xử lý</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach([
                            ['Kỳ lương chờ xử lý', $pendingPayrollPeriods, route('accountant.payroll-periods.index'), 'bg-amber-100 text-amber-800'],
                            ['Tạm ứng chờ duyệt', $pendingAdvances, route('accountant.advances.index'), 'bg-cyan-100 text-cyan-800'],
                            ['Đăng ký NPT chờ duyệt', $pendingNpt, route('accountant.tax.pending-registrations'), 'bg-violet-100 text-violet-800'],
                            ['HĐ hết hạn ≤30 ngày', $expiring['stats']['within_30'], route('accountant.contracts.expiring'), 'bg-rose-100 text-rose-800'],
                        ] as [$label, $count, $href, $badge])
                            <li>
                                <a href="{{ $href }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 transition hover:border-amber-200 hover:bg-amber-50/50">
                                    <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                    <span class="accountant-badge {{ $badge }}">{{ $count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="accountant-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Truy cập nhanh</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @foreach([
                            ['Lương', route('accountant.payroll-periods.index'), 'from-amber-500 to-orange-500'],
                            ['Bảo hiểm', route('accountant.insurance.index'), 'from-sky-500 to-indigo-500'],
                            ['Thuế', route('accountant.tax.index'), 'from-violet-500 to-purple-600'],
                            ['Báo cáo', route('accountant.reports.index'), 'from-emerald-500 to-teal-500'],
                        ] as [$label, $href, $tone])
                            <a href="{{ $href }}" class="rounded-xl bg-gradient-to-br {{ $tone }} px-3 py-3 text-center text-xs font-bold text-white shadow-sm transition hover:opacity-95">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-accountant-layout>
