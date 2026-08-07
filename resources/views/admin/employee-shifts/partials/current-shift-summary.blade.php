@props([
    'summaries' => [],
    'monthLabel' => '',
    'showEmployee' => true,
])

@if ($summaries !== [])
    <div class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50/80 to-indigo-50/50 p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-violet-500">Ca hiện tại</p>
                <h3 class="text-lg font-bold text-slate-800">Tháng {{ $monthLabel }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($summaries as $summary)
                <div class="rounded-2xl border border-white/80 bg-white/90 px-4 py-4 shadow-sm">
                    @if ($showEmployee)
                        <p class="text-sm font-bold text-slate-800">{{ $summary['employee_name'] }}</p>
                        <p class="text-xs text-slate-500">{{ $summary['employee_code'] }} · {{ $summary['department_name'] ?? '—' }}</p>
                    @endif
                    <p class="{{ $showEmployee ? 'mt-3' : '' }} text-base font-bold text-violet-700">
                        {{ $summary['shift_name'] }}
                        <span class="text-sm font-semibold text-violet-500">({{ $summary['weekday_pattern'] }})</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['time_range'] }} · {{ $summary['days_count'] }} ngày trong tháng
                    </p>
                    @if ($showEmployee && ! empty($summary['employee_id']))
                        <a href="{{ route('admin.employees.show', $summary['employee_id']) }}#lich-ca-lam"
                           class="mt-3 inline-flex text-xs font-semibold text-violet-600 hover:text-violet-800">
                            Xem lịch ca →
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
