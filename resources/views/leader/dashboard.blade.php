@php
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
@endphp

<x-leader-layout title="Dashboard Trưởng nhóm" subtitle="Tổng quan nhóm">
    <div class="leader-page">
        <section class="leader-hero">
            <div class="relative max-w-3xl">
                <h2 class="text-2xl font-bold sm:text-3xl">Chào {{ $firstName }}</h2>
                <p class="mt-2 text-sm text-violet-100/90">
                    Quản lý {{ $teamCount }} thành viên · theo dõi KPI, task và báo cáo nhóm.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('leader.employees.index') }}" class="leader-btn-primary">Xem nhân viên</a>
                    <a href="{{ route('leader.reports.index') }}" class="inline-flex items-center rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        Báo cáo nhóm
                    </a>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $teamCount, 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'KPI đang làm', 'value' => $kpiInProgress, 'tone' => 'text-sky-600'])
            @include('leader.partials.stat-card', ['label' => 'Task KPI', 'value' => $taskCount, 'tone' => 'text-fuchsia-600'])
            @include('leader.partials.stat-card', ['label' => 'Có mặt hôm nay', 'value' => $todayPresent, 'tone' => 'text-emerald-600'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card overflow-hidden">
                <div class="border-b border-violet-100/80 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Tiến độ KPI gần đây</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentKpis as $ek)
                        <a href="{{ route('leader.kpis.show', $ek) }}" class="flex items-center justify-between px-5 py-4 transition hover:bg-violet-50/40">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $ek->kpi?->title ?? 'KPI' }}</p>
                                <p class="text-xs text-slate-500">{{ $ek->employee?->full_name ?? '—' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-violet-700">{{ $ek->progress }}%</p>
                                <p class="text-[11px] text-slate-500">{{ $ek->status_label }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-slate-500">Chưa có KPI trong nhóm.</p>
                    @endforelse
                </div>
            </div>

            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Tóm tắt KPI</h3>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-2xl font-bold text-amber-700">{{ $kpiPending }}</p>
                        <p class="text-xs text-slate-500">Chờ</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-4">
                        <p class="text-2xl font-bold text-sky-700">{{ $kpiInProgress }}</p>
                        <p class="text-xs text-slate-500">Đang làm</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-2xl font-bold text-emerald-700">{{ $kpiCompleted }}</p>
                        <p class="text-xs text-slate-500">Hoàn thành</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-leader-layout>
