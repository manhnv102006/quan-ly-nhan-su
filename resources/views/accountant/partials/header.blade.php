@php
    $user = Auth::user();
    $initial = strtoupper(mb_substr($user?->name ?? 'U', 0, 1));
@endphp

<header class="accountant-header sticky top-0 z-50">
    <div class="flex h-[60px] items-center justify-between gap-3 px-4 lg:px-6">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-slate-500 hover:bg-amber-50 lg:hidden" aria-label="Mở menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <div class="min-w-0">
                <h1 class="truncate text-base font-bold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                @if (! empty($subtitle))<p class="hidden truncate text-xs text-slate-500 sm:block">{{ $subtitle }}</p>@endif
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            @include('admin.partials.notification-dropdown')
            <a href="{{ route('profile.edit') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-xs font-bold text-white shadow-sm">{{ $initial }}</a>
            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf<button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">Thoát</button></form>
        </div>
    </div>
    @if (session('success') || session('error'))
        <div class="border-t border-slate-100 px-4 py-2 lg:px-6"><p class="text-sm font-medium {{ session('error') ? 'text-rose-600' : 'text-emerald-600' }}">{{ session('error') ?? session('success') }}</p></div>
    @endif
</header>
