@php
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
    $kpiChartMax = max(1, collect($kpiStatusChart)->max('value'));
    $attendanceRate = $teamCount > 0 ? round(($todayCheckedIn / $teamCount) * 100) : 0;
@endphp

<x-leader-layout title="Dashboard Trưởng nhóm" subtitle="Thống kê nhóm · Thành viên · KPI · Công việc">
    <div class="leader-page">
        {{-- Hero --}}
        <section class="leader-hero">
            <div class="absolute -right-16 top-0 h-56 w-56 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-violet-400/15 blur-3xl"></div>
            <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-violet-300"></span>
                        Tổng quan điều hành nhóm
                    </span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Chào {{ $firstName }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-violet-100/90">
                        Quản lý <strong class="text-white">{{ $teamCount }}</strong> thành viên
                        · <strong class="text-white">{{ $kpiTotal }}</strong> KPI
                        · <strong class="text-white">{{ $taskCount }}</strong> task
                        · TB tiến độ <strong class="text-white">{{ $avgKpiProgress }}%</strong>
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('leader.employees.index') }}" class="leader-btn-primary">Xem nhân viên</a>
                        <a href="{{ route('leader.kpis.index') }}" class="inline-flex items-center rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                            KPI nhóm
                        </a>
                        <a href="{{ route('leader.reports.index') }}" class="inline-flex items-center rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                            Báo cáo nhóm
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:w-[420px]">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-violet-200">Chấm công hôm nay</p>
                        <p class="mt-2 text-2xl font-extrabold">{{ $todayCheckedIn }}/{{ $teamCount }}</p>
                        <p class="mt-1 text-sm text-violet-100/85">{{ $attendanceRate }}% có mặt · {{ $todayLate }} đi muộn</p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-violet-200">KPI tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</p>
                        <p class="mt-2 text-2xl font-extrabold">{{ $kpiCompleted }}/{{ $kpiTotal }}</p>
                        <p class="mt-1 text-sm text-violet-100/85">{{ $kpiInProgress }} đang làm · TB {{ $avgKpiProgress }}%</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Thống kê tổng quan --}}
        <div>
            <div class="mb-4">
                <h3 class="text-lg font-bold text-slate-900">Thống kê nhóm</h3>
                <p class="text-sm text-slate-500">Thành viên, KPI, công việc và chấm công tháng {{ $month }}/{{ $year }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-6">
                @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $teamCount, 'note' => $activeMembers.' đang làm', 'tone' => 'text-violet-700'])
                @include('leader.partials.stat-card', ['label' => 'KPI tổng', 'value' => $kpiTotal, 'note' => $kpiInProgress.' đang thực hiện', 'tone' => 'text-sky-600'])
                @include('leader.partials.stat-card', ['label' => 'KPI hoàn thành', 'value' => $kpiCompleted, 'note' => 'TB '.$avgKpiProgress.'% tiến độ', 'tone' => 'text-emerald-600'])
                @include('leader.partials.stat-card', ['label' => 'Task KPI', 'value' => $taskCount, 'note' => 'Công việc theo KPI', 'tone' => 'text-fuchsia-600'])
                @include('leader.partials.stat-card', ['label' => 'Có mặt hôm nay', 'value' => $todayCheckedIn, 'note' => $todayAbsent.' chưa check-in', 'tone' => 'text-emerald-600'])
                @include('leader.partials.stat-card', ['label' => 'Ngày công tháng', 'value' => $monthlyWorkDays, 'note' => $monthlyLateDays.' lần muộn', 'tone' => 'text-indigo-600'])
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Biểu đồ KPI --}}
            <div class="leader-card p-5 xl:col-span-3">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Phân bổ KPI nhóm</h3>
                        <p class="text-xs text-slate-500">Theo trạng thái · {{ $kpiTotal }} KPI</p>
                    </div>
                    <a href="{{ route('leader.kpis.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">Xem tất cả →</a>
                </div>

                @if($kpiTotal > 0)
                    <div class="flex h-48 items-end gap-3 border-b border-slate-100 pb-2 sm:gap-4">
                        @foreach($kpiStatusChart as $item)
                            @php $height = max(8, ($item['value'] / $kpiChartMax) * 100); @endphp
                            <div class="group flex flex-1 flex-col items-center gap-2">
                                <span class="text-[10px] font-bold {{ $item['text'] }} opacity-0 transition group-hover:opacity-100">{{ $item['value'] }}</span>
                                <div class="w-full max-w-[72px] rounded-t-xl bg-gradient-to-t {{ $item['color'] }} shadow-md transition-all" style="height: {{ $height }}%"></div>
                                <span class="text-[10px] font-semibold text-slate-500 sm:text-xs">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach($kpiStatusChart as $item)
                            <div class="rounded-xl {{ $item['bg'] }} px-3 py-2.5 text-center">
                                <p class="text-xl font-extrabold {{ $item['text'] }}">{{ $item['value'] }}</p>
                                <p class="text-[11px] text-slate-500">{{ $item['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-14 text-center text-sm text-slate-400">Chưa có KPI được giao cho nhóm.</p>
                @endif
            </div>

            {{-- Chấm công + việc cần theo dõi --}}
            <div class="space-y-4 xl:col-span-2">
                <div class="leader-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Chấm công hôm nay</h3>
                    <p class="mb-4 text-xs text-slate-500">{{ now()->format('d/m/Y') }}</p>
                    <dl class="space-y-3">
                        @foreach([
                            ['Đúng giờ', $todayPresent, 'text-emerald-700', 'bg-emerald-50'],
                            ['Đi muộn', $todayLate, 'text-amber-700', 'bg-amber-50'],
                            ['Chưa check-in', $todayAbsent, 'text-rose-700', 'bg-rose-50'],
                        ] as [$label, $count, $tone, $bg])
                            <div class="flex items-center justify-between rounded-xl {{ $bg }} px-4 py-3">
                                <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                                <dd class="text-sm font-bold {{ $tone }}">{{ $count }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-xs text-slate-500">
                            <span>Tỷ lệ có mặt</span>
                            <span class="font-bold text-violet-700">{{ $attendanceRate }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-all" style="width: {{ $attendanceRate }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="leader-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Cần theo dõi</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach([
                            ['KPI chưa hoàn thành', $kpiPending + $kpiInProgress, route('leader.kpis.index'), 'bg-sky-100 text-sky-800'],
                            ['KPI quá hạn', $kpiNotCompleted, route('leader.kpis.index', ['status' => 'not_completed']), 'bg-rose-100 text-rose-800'],
                            ['Đề xuất chờ duyệt', $pendingTeamRequests, route('leader.team-requests.index'), 'bg-amber-100 text-amber-800'],
                            ['Task KPI', $taskCount, route('leader.tasks.index'), 'bg-fuchsia-100 text-fuchsia-800'],
                        ] as [$label, $count, $href, $badge])
                            <li>
                                <a href="{{ $href }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 transition hover:border-violet-200 hover:bg-violet-50/50">
                                    <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                    <span class="leader-badge {{ $badge }}">{{ $count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- KPI gần đây --}}
            <div class="leader-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-violet-100/80 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Tiến độ KPI gần đây</h3>
                        <p class="text-xs text-slate-500">Cập nhật mới nhất của nhóm</p>
                    </div>
                    <a href="{{ route('leader.kpis.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">Chi tiết →</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentKpis as $ek)
                        <a href="{{ route('leader.kpis.show', $ek) }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-violet-50/40">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-xs font-bold text-violet-700">
                                {{ $ek->progress }}%
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-800">{{ $ek->kpi?->title ?? 'KPI' }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $ek->employee?->full_name ?? '—' }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] font-semibold text-slate-500">{{ $ek->status_label }}</span>
                        </a>
                    @empty
                        <p class="px-5 py-12 text-center text-sm text-slate-400">Chưa có KPI trong nhóm.</p>
                    @endforelse
                </div>
            </div>

            {{-- Thành viên nhóm --}}
            <div class="leader-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-violet-100/80 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Thành viên nhóm</h3>
                        <p class="text-xs text-slate-500">{{ $activeMembers }} đang làm · {{ $inactiveMembers }} tạm nghỉ/nghỉ</p>
                    </div>
                    <a href="{{ route('leader.employees.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">Tất cả →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[420px] text-sm">
                        <thead>
                            <tr class="bg-violet-50/60 text-left text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3">Nhân viên</th>
                                <th class="px-5 py-3 text-center">KPI</th>
                                <th class="px-5 py-3 text-center">Tiến độ</th>
                                <th class="px-5 py-3 text-center">Hôm nay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($memberRows as $row)
                                <tr class="hover:bg-violet-50/30">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('leader.employees.show', $row['employee']) }}" class="font-semibold text-violet-800 hover:underline">
                                            {{ $row['employee']->full_name }}
                                        </a>
                                        <p class="text-[11px] text-slate-400">{{ $row['employee']->department?->department_name ?? '—' }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-emerald-700">{{ $row['kpi_completed'] }}</span>
                                        <span class="text-slate-400">/{{ $row['kpi_total'] }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center font-semibold text-violet-700">{{ $row['kpi_avg_progress'] }}%</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($row['present_today'])
                                            <span class="leader-badge bg-emerald-100 text-emerald-700">Có mặt</span>
                                        @else
                                            <span class="leader-badge bg-slate-100 text-slate-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">
                                        Chưa có thành viên trong nhóm.
                                        <a href="{{ route('leader.team-requests.index') }}" class="mt-1 block font-semibold text-violet-700 hover:underline">Đề xuất thêm thành viên →</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Truy cập nhanh --}}
        <div class="leader-card p-5">
            <h3 class="text-sm font-bold text-slate-800">Truy cập nhanh</h3>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([
                    ['Nhân viên', route('leader.employees.index'), 'from-violet-500 to-purple-600'],
                    ['Lịch nhóm', route('leader.team-schedule.index'), 'from-sky-500 to-indigo-500'],
                    ['KPI', route('leader.kpis.index'), 'from-emerald-500 to-teal-500'],
                    ['Task', route('leader.tasks.index'), 'from-fuchsia-500 to-pink-500'],
                ] as [$label, $href, $tone])
                    <a href="{{ $href }}" class="rounded-xl bg-gradient-to-br {{ $tone }} px-4 py-4 text-center text-sm font-bold text-white shadow-sm transition hover:opacity-95">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-leader-layout>
