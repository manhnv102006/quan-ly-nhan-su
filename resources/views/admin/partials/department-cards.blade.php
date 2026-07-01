@props([
    'departmentSummaries',
    'routeName',
    'routeParams' => [],
    'statLabels' => ['Tổng đơn', 'Chờ duyệt', 'Đã duyệt'],
    'statKeys' => ['total', 'pending', 'approved'],
    'statTones' => ['slate', 'amber', 'emerald'],
])

<section class="space-y-4">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Theo phòng ban</p>
        <h2 class="mt-1 text-lg font-bold text-slate-800">Bấm vào phòng ban để xem chi tiết</h2>
    </div>

    @if ($departmentSummaries->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
            Chưa có phòng ban nào.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($departmentSummaries as $summary)
                @php
                    $dept = $summary['department'];
                    $deptStats = $summary['stats'];
                    $url = route($routeName, array_merge($routeParams, ['department' => $dept->id]));
                @endphp
                <a href="{{ $url }}"
                   class="group block rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-violet-300 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-sm font-bold text-white shadow-sm">
                            {{ strtoupper(mb_substr($dept->department_name, 0, 1)) }}
                        </div>
                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-violet-600 group-hover:bg-violet-100">
                            Xem chi tiết →
                        </span>
                    </div>

                    <h3 class="mt-4 font-bold text-slate-800 group-hover:text-violet-700 transition">
                        {{ $dept->department_name }}
                    </h3>
                    <p class="text-xs text-slate-500">{{ $dept->department_code }}</p>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        @foreach ($statKeys as $i => $key)
                            @php
                                $tone = $statTones[$i] ?? 'slate';
                                $bgClass = match ($tone) {
                                    'amber' => 'bg-amber-50',
                                    'emerald' => 'bg-emerald-50',
                                    'rose' => 'bg-rose-50',
                                    'sky' => 'bg-sky-50',
                                    default => 'bg-slate-50',
                                };
                                $textClass = match ($tone) {
                                    'amber' => 'text-amber-600',
                                    'emerald' => 'text-emerald-600',
                                    'rose' => 'text-rose-600',
                                    'sky' => 'text-sky-600',
                                    default => 'text-slate-800',
                                };
                            @endphp
                            <div class="rounded-xl {{ $bgClass }} px-2 py-2">
                                <p class="text-lg font-extrabold {{ $textClass }}">{{ $deptStats[$key] ?? 0 }}</p>
                                <p class="text-[10px] text-slate-500">{{ $statLabels[$i] ?? $key }}</p>
                            </div>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
