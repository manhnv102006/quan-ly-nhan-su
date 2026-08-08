@php
    $user = Auth::user();
    $initial = strtoupper(mb_substr($user?->name ?? 'U', 0, 1));
@endphp

<header class="employee-header sticky top-0 z-50">
    <div class="flex h-[60px] items-center justify-between gap-3 px-4 lg:px-6">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="rounded-xl p-2 text-slate-500 hover:bg-sky-50 hover:text-sky-700 lg:hidden"
                aria-label="Mở menu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="min-w-0">
                <h1 class="truncate text-base font-bold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                @if (! empty($subtitle))
                    <p class="hidden truncate text-xs text-slate-500 sm:block">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            @if (($employeePendingActions['total'] ?? 0) > 0)
                <a href="{{ route('employee.kpis.index') }}"
                   title="KPI cần cập nhật"
                   class="relative rounded-xl border border-slate-200 bg-white p-2 text-slate-500 shadow-sm hover:border-sky-200 hover:text-sky-700">
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">!</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z" />
                    </svg>
                </a>
            @endif

            @include('admin.partials.notification-dropdown')

            <a href="{{ route('profile.edit') }}"
               class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-xs font-bold text-white shadow-sm shadow-sky-500/30">
                {{ $initial }}
            </a>

            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                @csrf
                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                    Thoát
                </button>
            </form>
        </div>
    </div>

    @if (session('success') || session('error'))
        <div class="border-t border-slate-100 px-4 py-2 lg:px-6">
            <p class="text-sm font-medium {{ session('error') ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ session('error') ?? session('success') }}
            </p>
        </div>
    @endif
</header>
