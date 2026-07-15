<x-leader-layout title="{{ $employee->full_name }}" subtitle="Chi tiết nhân viên">
    <div class="leader-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('leader.employees.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">← Nhân viên</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->department?->department_name ?? '—' }}
                    · {{ $employee->position?->position_name ?? '—' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">KPI gần đây</h3>
                <div class="mt-4 space-y-3">
                    @forelse($kpis as $kpi)
                        <a href="{{ route('leader.kpis.show', $kpi) }}" class="block rounded-xl border border-violet-100 p-4 transition hover:bg-violet-50/40">
                            <p class="font-semibold text-slate-800">{{ $kpi->kpi?->title ?? 'KPI' }}</p>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ $kpi->status_label }}</span>
                                <span class="font-bold text-violet-700">{{ $kpi->progress }}%</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Chưa có KPI.</p>
                    @endforelse
                </div>
            </div>

            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Chấm công gần đây</h3>
                <div class="mt-4 space-y-2">
                    @forelse($attendances as $att)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                            <span>{{ $att->attendance_date?->format('d/m/Y') }}</span>
                            <span class="font-semibold">{{ $att->status }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Chưa có dữ liệu chấm công.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-leader-layout>
