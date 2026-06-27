@php
    $typeMeta = \App\Support\NotificationTypeMeta::all();
    $compact = $compact ?? false;
    $readRoute = $readRoute ?? 'notifications.read';
    $showRoute = $showRoute ?? null;
    $meta = $typeMeta[$notification->type] ?? $typeMeta['system'];
    $isRead = (bool) $notification->is_read;
    $wrapperTag = $showRoute ? 'a' : 'div';
    $wrapperClass = $showRoute
        ? 'group flex gap-3 transition hover:bg-slate-50 block'
        : 'group flex gap-3 transition hover:bg-slate-50';
@endphp

<{{ $wrapperTag }}
    @if ($showRoute)
        href="{{ route($showRoute, $notification->id) }}"
    @endif
    @class([
        $wrapperClass,
        'px-4 py-3.5' => $compact,
        'px-6 py-5' => ! $compact,
        'bg-violet-50/40' => ! $isRead && ! $showRoute,
        'bg-sky-50/40' => ! $isRead && $showRoute,
    ])
>
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $meta['icon'] }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['path'] }}" />
        </svg>
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p @class([
                    'font-semibold text-slate-800 leading-snug',
                    'text-sm' => $compact,
                    'text-base' => ! $compact,
                    'text-violet-900' => ! $isRead && ! $showRoute,
                    'text-sky-900' => ! $isRead && $showRoute,
                ])>
                    {{ $notification->title }}
                </p>
                <p @class([
                    'mt-1 text-slate-600 leading-relaxed',
                    'text-xs line-clamp-2' => $compact,
                    'text-sm line-clamp-2' => ! $compact && $showRoute,
                    'text-sm' => ! $compact && ! $showRoute,
                ])>
                    {{ $notification->content }}
                </p>
            </div>
            @unless ($isRead)
                <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-rose-500"></span>
            @endunless
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $meta['badge'] }}">
                {{ $meta['label'] }}
            </span>
            <span class="text-[11px] text-slate-400">
                @if ($compact)
                    {{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}
                @else
                    {{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}
                @endif
            </span>
            @if ($showRoute && ! $compact)
                <span class="ml-auto text-xs font-medium text-sky-600 group-hover:text-sky-700">
                    Xem chi tiết →
                </span>
            @elseif (! $compact && ! $isRead && $notification->notification_user_id && ! $showRoute)
                <form action="{{ route($readRoute, $notification->id) }}" method="POST" class="ml-auto" @click.stop>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-xs font-medium text-violet-600 hover:text-violet-700">
                        Đánh dấu đã đọc
                    </button>
                </form>
            @endif
        </div>
    </div>
</{{ $wrapperTag }}>
