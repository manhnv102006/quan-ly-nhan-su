@php
    $headerNotifications = $headerNotifications ?? collect();
    $headerUnreadCount = $headerUnreadCount ?? 0;
    $user = auth()->user();
    $notificationsIndexRoute = match (true) {
        $user?->isAdmin() => 'notifications.index',
        $user?->isManager() => 'manager.notifications.index',
        $user?->isAccountant(), $user?->isEmployee() => 'employee.notifications.index',
        default => 'notifications.index',
    };
    $notificationsShowRoute = match (true) {
        $user?->isAdmin() => 'notifications.show',
        $user?->isManager() => 'manager.notifications.show',
        $user?->isAccountant(), $user?->isEmployee() => 'employee.notifications.show',
        default => null,
    };
    $notificationsShowAccent = match (true) {
        $user?->isAdmin() => 'violet',
        $user?->isManager() => 'emerald',
        $user?->isAccountant() => 'amber',
        $user?->isEmployee() => 'sky',
        default => 'sky',
    };
@endphp

<div class="relative" x-data="{ open: false }">
    <button type="button"
            @click.stop="open = !open"
            :aria-expanded="open"
            aria-haspopup="true"
            aria-label="Thông báo"
            class="relative rounded-2xl border border-white/70 bg-white/70 p-2.5 text-slate-500 shadow-sm transition hover:bg-violet-50 hover:text-violet-600"
            :class="open ? 'bg-violet-50 text-violet-600 ring-4 ring-violet-500/10' : ''">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($headerUnreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                {{ $headerUnreadCount > 9 ? '9+' : $headerUnreadCount }}
            </span>
        @endif
    </button>

    <div x-cloak
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         @click.outside="open = false"
         @keydown.escape.window="open = false"
         class="absolute right-0 top-full z-[200] mt-2 w-[min(100vw-2rem,24rem)] origin-top-right">
        <div class="overflow-hidden rounded-3xl border border-white/80 bg-white/95 shadow-2xl shadow-slate-300/40 backdrop-blur-xl">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-violet-50/90 to-white px-4 py-3.5">
                <div>
                    <p class="text-sm font-bold text-slate-800">Thông báo</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if ($headerUnreadCount > 0)
                            {{ $headerUnreadCount }} chưa đọc
                        @else
                            Tất cả đã đọc
                        @endif
                    </p>
                </div>
                @if ($headerUnreadCount > 0)
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                        Mới
                    </span>
                @endif
            </div>

            <div class="max-h-[22rem] overflow-y-auto divide-y divide-slate-100">
                @forelse ($headerNotifications as $notification)
                    @include('admin.partials.notification-item', [
                        'notification' => $notification,
                        'compact' => true,
                        'showRoute' => $notificationsShowRoute,
                        'showAccent' => $notificationsShowAccent,
                    ])
                @empty
                    <div class="px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-500">Chưa có thông báo</p>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-100 bg-slate-50/80 px-4 py-3">
                <a href="{{ route($notificationsIndexRoute) }}"
                   @click.stop="open = false"
                   class="flex w-full items-center justify-center rounded-2xl bg-white px-4 py-2.5 text-xs font-semibold text-violet-600 shadow-sm ring-1 ring-slate-200/80 transition hover:bg-violet-50 hover:text-violet-700">
                    Xem tất cả thông báo
                </a>
            </div>
        </div>
    </div>
</div>
