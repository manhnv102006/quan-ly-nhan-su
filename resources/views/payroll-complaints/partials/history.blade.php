@php
    $timeline = $complaint->historyTimeline();
    $toneClasses = [
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        'violet' => 'border-violet-200 bg-violet-50 text-violet-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
    ];
    $dotClasses = [
        'sky' => 'bg-sky-500',
        'indigo' => 'bg-indigo-500',
        'emerald' => 'bg-emerald-500',
        'rose' => 'bg-rose-500',
        'violet' => 'bg-violet-500',
        'amber' => 'bg-amber-500',
    ];
@endphp

<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <h3 class="text-sm font-bold text-slate-800">Lịch sử xử lý</h3>
    <p class="mt-1 text-xs text-slate-500">Theo dõi các bước xử lý khiếu nại từ lúc gửi đến khi hoàn tất.</p>

    <ol class="mt-5 space-y-0">
        @foreach ($timeline as $index => $event)
            @php
                $tone = $event['tone'] ?? 'sky';
                $isPending = $event['pending'] ?? false;
                $isLast = $loop->last;
            @endphp
            <li class="relative flex gap-4 pb-6 {{ $isLast ? 'pb-0' : '' }}">
                @unless ($isLast)
                    <span class="absolute left-[11px] top-6 h-[calc(100%-12px)] w-0.5 bg-slate-200" aria-hidden="true"></span>
                @endunless

                <span class="relative z-10 mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $dotClasses[$tone] ?? 'bg-slate-400' }} ring-4 ring-white">
                    @if ($isPending)
                        <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                    @else
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-slate-800">{{ $event['title'] }}</p>
                        @if ($isPending)
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $toneClasses[$tone] ?? $toneClasses['amber'] }}">
                                Đang chờ
                            </span>
                        @elseif ($event['at'] ?? null)
                            <span class="text-xs text-slate-400">{{ $event['at']->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>

                    @if ($event['actor'] ?? null)
                        <p class="mt-1 text-xs text-slate-500">Bởi: {{ $event['actor'] }}</p>
                    @endif

                    @if (filled($event['description'] ?? null))
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $event['description'] }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
