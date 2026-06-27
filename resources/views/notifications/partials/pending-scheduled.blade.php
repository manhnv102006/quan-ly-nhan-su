@if ($pendingScheduled->isNotEmpty())
    <div class="rounded-3xl border border-amber-100 bg-amber-50/40 overflow-hidden">
        <div class="border-b border-amber-100 px-6 py-4">
            <h3 class="font-semibold text-amber-900">Thông báo đang chờ gửi</h3>
            <p class="text-sm text-amber-700 mt-0.5">{{ $pendingScheduled->count() }} thông báo sẽ được gửi tự động đúng giờ</p>
        </div>
        <div class="divide-y divide-amber-100/80">
            @foreach ($pendingScheduled as $item)
                <div class="px-6 py-4 flex flex-wrap items-start justify-between gap-3 bg-white/60">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-800">{{ $item->title }}</p>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $item->content }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-amber-800">
                            {{ $item->scheduled_at?->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $item->scheduled_at?->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
