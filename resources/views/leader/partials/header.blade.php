@php
    $user = Auth::user();
    $initial = strtoupper(mb_substr($user?->name ?? 'U', 0, 1));
@endphp

<header class="leader-header sticky top-0 z-50">
    <div class="flex h-[76px] items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 flex-1 items-center gap-4">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="rounded-xl p-2.5 text-slate-500 transition hover:bg-violet-50 hover:text-violet-700 lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="min-w-0">
                <h1 class="truncate text-xl font-extrabold tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                <p class="hidden text-xs text-slate-500 sm:block">
                    Xin chào <span class="font-semibold text-violet-700">{{ $user->name }}</span>
                    @if (! empty($subtitle)) · {{ $subtitle }} @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="hidden items-center gap-1.5 rounded-full bg-gradient-to-r from-violet-100 to-fuchsia-100 px-3 py-1.5 text-xs font-semibold text-violet-800 sm:inline-flex">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-violet-500"></span>
                Trưởng nhóm
            </span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-fuchsia-500 to-purple-600 text-sm font-bold text-white shadow-md shadow-violet-500/25 ring-2 ring-white">
                {{ $initial }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                    Đăng xuất
                </button>
            </form>
        </div>
    </div>
    @if (session('success') || session('error'))
        <div class="px-4 pb-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-sm {{ session('error') ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                <span>{{ session('error') ?? session('success') }}</span>
            </div>
        </div>
    @endif
</header>
