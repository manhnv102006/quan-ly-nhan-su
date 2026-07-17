<x-leader-layout title="Chi tiết KPI" subtitle="{{ $employeeKpi->kpi?->title }}">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('leader.kpis.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">← Tiến độ KPI cá nhân</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $employeeKpi->kpi?->title ?? 'KPI' }}</h2>
                <p class="text-sm text-slate-500">{{ $employeeKpi->employee?->full_name ?? '—' }}</p>
            </div>
            <a href="{{ route('leader.kpis.score.edit', $employeeKpi) }}" class="leader-btn-primary">Chấm điểm KPI</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Tiến độ', 'value' => $employeeKpi->progress.'%', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Điểm Leader', 'value' => $employeeKpi->leader_score ?? '—'])
            @include('leader.partials.stat-card', ['label' => 'Trạng thái', 'value' => $employeeKpi->status_label])
            @include('leader.partials.stat-card', ['label' => 'Hạn', 'value' => $employeeKpi->deadline?->format('d/m/Y') ?? '—'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Thông tin KPI cá nhân</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Mục tiêu</dt><dd class="font-semibold">{{ $employeeKpi->target ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Mô tả KPI</dt><dd>{{ $employeeKpi->kpi?->description ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Ghi chú phân bổ</dt><dd>{{ $employeeKpi->comment ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Nhận xét Leader</dt><dd>{{ $employeeKpi->leader_review ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Task / Công việc</h3>
                <ul class="mt-4 space-y-3">
                    @forelse($employeeKpi->kpi?->tasks ?? [] as $task)
                        <li class="rounded-xl border border-violet-100 bg-violet-50/30 px-4 py-3">
                            <p class="font-semibold text-slate-800">{{ $task->title }}</p>
                            @if($task->description)
                                <p class="mt-1 text-xs text-slate-500">{{ $task->description }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">KPI chưa có task chi tiết.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @if($employeeKpi->kpiAssignment)
            <div class="text-center">
                <a href="{{ route('leader.team-kpis.show', $employeeKpi->kpiAssignment) }}" class="text-sm font-semibold text-violet-700 hover:underline">Xem KPI nhóm gốc →</a>
            </div>
        @endif
    </div>
</x-leader-layout>
