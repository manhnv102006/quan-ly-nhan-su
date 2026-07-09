@props([
    'departmentSummaries',
    'routeName',
    'routeParams' => [],
    'statLabels' => ['Tổng đơn', 'Chờ duyệt', 'Đã duyệt'],
    'statKeys' => ['total', 'pending', 'approved'],
    'statTones' => ['slate', 'amber', 'emerald'],
    'formatters' => [],
])

<section class="space-y-4">
    <div>
        <p class="admin-kicker">Theo phòng ban</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">Bấm vào phòng ban để xem chi tiết</h2>
    </div>

    @if ($departmentSummaries->isEmpty())
        <div class="admin-empty-state rounded-[1.5rem] border border-dashed border-slate-200 bg-white/70">
            <div class="admin-empty-icon">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75" />
                </svg>
            </div>
            <p class="admin-empty-title">Chưa có phòng ban nào</p>
            <p class="admin-empty-text">Dữ liệu sẽ hiển thị tại đây sau khi có phòng ban.</p>
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
                   class="group block overflow-hidden rounded-[1.5rem] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/60 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:border-violet-200 hover:bg-white hover:shadow-xl hover:shadow-violet-200/30">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-500 text-sm font-black text-white shadow-lg shadow-violet-500/20 transition group-hover:scale-105">
                            {{ strtoupper(mb_substr($dept->department_name, 0, 1)) }}
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-violet-600 transition group-hover:bg-violet-100">
                            Xem
                            <svg class="h-3 w-3 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </span>
                    </div>

                    <h3 class="mt-4 font-bold text-slate-800 group-hover:text-violet-700 transition">
                        {{ $dept->department_name }}
                    </h3>
                    <p class="text-xs text-slate-500">{{ $dept->department_code }}</p>

                    @if(count($statKeys) > 3)
                        <!-- Dạng danh sách chi tiết (khi có nhiều thông số như Lương) -->
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 pt-3">
                            @foreach ($statKeys as $i => $key)
                                @php
                                    $tone = $statTones[$i] ?? 'slate';
                                    $textClass = match ($tone) {
                                        'amber' => 'text-amber-600 font-semibold',
                                        'emerald' => 'text-emerald-600 font-semibold',
                                        'rose' => 'text-rose-500 font-semibold',
                                        'sky' => 'text-sky-600 font-semibold',
                                        'indigo' => 'text-indigo-600 font-semibold',
                                        'violet' => 'text-violet-600 font-bold text-sm',
                                        default => 'text-slate-700 font-semibold',
                                    };
                                    $val = $deptStats[$key] ?? 0;
                                    if (isset($formatters[$key])) {
                                        $val = $formatters[$key]($val);
                                    }
                                @endphp
                                <div class="flex items-center justify-between rounded-xl px-2 py-1 text-xs transition group-hover:bg-slate-50">
                                    <span class="text-slate-500">{{ $statLabels[$i] ?? $key }}</span>
                                    <span class="{{ $textClass }}">{{ $val }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Dạng lưới (cho KPI, nghỉ phép, tăng ca...) -->
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            @foreach ($statKeys as $i => $key)
                                @php
                                    $val = $deptStats[$key] ?? 0;
                                    $tone = $statTones[$i] ?? 'slate';
                                    $bgClass = match ($tone) {
                                        'amber' => 'bg-amber-50',
                                        'emerald' => 'bg-emerald-50',
                                        'rose' => 'bg-rose-50',
                                        'sky' => 'bg-sky-50',
                                        'violet' => 'bg-violet-50',
                                        default => 'bg-slate-50',
                                    };
                                    $textClass = match ($tone) {
                                        'amber' => 'text-amber-600',
                                        'emerald' => 'text-emerald-600',
                                        'rose' => 'text-rose-600',
                                        'sky' => 'text-sky-600',
                                        'violet' => 'text-violet-600',
                                        default => 'text-slate-800',
                                    };
                                    if ($key === 'status_label') {
                                        $bgClass = match ($val) {
                                            'Tạm tính' => 'bg-sky-100/70',
                                            'Đã tính' => 'bg-amber-100/70',
                                            'Đã duyệt' => 'bg-violet-100/70',
                                            'Đã chi trả' => 'bg-emerald-100/70',
                                            default => 'bg-slate-100/70',
                                        };
                                        $textClass = match ($val) {
                                            'Tạm tính' => 'text-sky-700 font-bold',
                                            'Đã tính' => 'text-amber-700 font-bold',
                                            'Đã duyệt' => 'text-violet-700 font-bold',
                                            'Đã chi trả' => 'text-emerald-700 font-bold',
                                            default => 'text-slate-600 font-bold',
                                        };
                                    }
                                    if (isset($formatters[$key])) {
                                        $val = $formatters[$key]($val);
                                    }
                                @endphp
                                <div class="flex min-h-[64px] flex-col items-center justify-center rounded-2xl {{ $bgClass }} px-2 py-2.5 ring-1 ring-white/70">
                                    <p class="text-xs text-slate-500 mb-1 font-medium">{{ $statLabels[$i] ?? $key }}</p>
                                    <p class="{{ $key === 'status_label' ? 'text-sm font-bold' : 'text-xl font-extrabold' }} {{ $textClass }}">{{ $val }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</section>
