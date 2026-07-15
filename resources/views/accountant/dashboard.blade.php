@php
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
@endphp

<x-accountant-layout title="Dashboard Kế toán" subtitle="Tổng quan tài chính & nhân sự">
    <div class="accountant-page">
        <section class="accountant-hero">
            <div class="absolute -right-16 top-0 h-56 w-56 rounded-full bg-amber-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-44 w-44 rounded-full bg-indigo-500/15 blur-3xl"></div>
            <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                        Bảng điều khiển kế toán
                    </span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Chào {{ $firstName }}, đây là tổng quan lương, bảo hiểm và chấm công tháng {{ now()->format('m/Y') }}.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-amber-100/90">
                        Theo dõi kỳ lương, hợp đồng sắp hết hạn, ngày công và các báo cáo tài chính trong một nơi.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('accountant.payroll-periods.index') }}" class="accountant-btn-primary !shadow-black/10">Quản lý lương</a>
                        <a href="{{ route('accountant.reports.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                            Xem báo cáo
                        </a>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:w-[380px]">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-200">Kỳ lương hiện tại</p>
                        <p class="mt-2 text-lg font-bold">{{ $currentPeriod?->name ?? 'Chưa tạo kỳ' }}</p>
                        <p class="mt-1 text-sm text-amber-100/85">{{ $currentPeriod?->status ?? '—' }}</p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-200">Nhân viên active</p>
                        <p class="mt-2 text-lg font-bold">{{ number_format($activeEmployees) }}</p>
                        <p class="mt-1 text-sm text-amber-100/85">{{ $departmentCount }} phòng ban</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Quỹ lương tháng', 'value' => $formatMoney($totalPayrollThisMonth), 'note' => 'Tổng thực lĩnh kỳ hiện tại', 'tone' => 'text-amber-700'])
            @include('accountant.partials.stat-card', ['label' => 'Kỳ chờ xử lý', 'value' => number_format($pendingPayrollPeriods), 'note' => 'Open / Calculated', 'tone' => 'text-orange-600'])
            @include('accountant.partials.stat-card', ['label' => 'Chấm công hôm nay', 'value' => number_format($todayAttendance), 'note' => now()->format('d/m/Y'), 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'HĐ sắp hết hạn', 'value' => number_format($expiringContracts), 'note' => 'Trong 30 ngày', 'tone' => 'text-rose-600'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="accountant-card overflow-hidden xl:col-span-2">
                <div class="border-b border-amber-50 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Kỳ lương gần đây</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="bg-amber-50/60 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-5 py-3">Kỳ</th>
                                <th class="px-5 py-3">Trạng thái</th>
                                <th class="px-5 py-3 text-right">Tổng chi</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentPeriods as $period)
                                <tr class="hover:bg-amber-50/30">
                                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $period->name }}</td>
                                    <td class="px-5 py-3">
                                        <span class="accountant-badge bg-amber-100 text-amber-800">{{ $period->status }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold">{{ $formatMoney($period->payrolls_sum_total_salary ?? 0) }}</td>
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

            <div class="accountant-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Truy cập nhanh</h3>
                <ul class="mt-4 space-y-2">
                    @foreach([
                        ['Bảo hiểm', route('accountant.insurance.index'), 'from-sky-500 to-indigo-500'],
                        ['Thuế TNCN', route('accountant.tax.index'), 'from-violet-500 to-purple-600'],
                        ['Tạm ứng lương', route('accountant.advances.index'), 'from-cyan-500 to-blue-500'],
                        ['Hợp đồng', route('accountant.contracts.index'), 'from-rose-500 to-pink-500'],
                        ['Chấm công', route('accountant.attendance.index'), 'from-emerald-500 to-teal-500'],
                        ['Kỳ lương', route('accountant.payroll-periods.index'), 'from-amber-500 to-orange-500'],
                    ] as [$label, $href, $tone])
                        <li>
                            <a href="{{ $href }}" class="flex items-center justify-between rounded-xl border border-amber-50 bg-gradient-to-r {{ $tone }} px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-95">
                                {{ $label }}
                                <span>→</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-accountant-layout>
