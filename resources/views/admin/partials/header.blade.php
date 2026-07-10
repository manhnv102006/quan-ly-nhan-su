<header class="admin-header sticky top-0 z-50 overflow-visible">
    <div class="flex h-16 items-center justify-between gap-2 px-3 sm:h-[76px] sm:px-6 lg:px-8">
        <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-4">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="shrink-0 rounded-xl p-2.5 text-slate-500 transition hover:bg-violet-50 hover:text-violet-600 lg:hidden"
                aria-label="Mở menu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-extrabold tracking-tight text-slate-900 sm:text-xl">{{ $title ?? 'Dashboard' }}</h1>
                <p class="hidden text-xs text-slate-500 sm:block">
                    Xin chào <span class="font-semibold text-violet-600">{{ Auth::user()->name }}</span>
                </p>
            </div>

            {{-- Search (decorative) --}}
            <div class="ml-4 hidden max-w-md flex-1 md:flex">
                <div class="relative w-full">
                    <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        type="text"
                        placeholder="Tìm kiếm nhanh..."
                        class="w-full rounded-2xl border border-white/70 bg-white/75 py-2.5 pl-10 pr-4 text-sm text-slate-600 shadow-sm shadow-slate-200/50 outline-none transition placeholder:text-slate-400 focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                    >
                </div>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
            @php
                $adminPending = Auth::user()?->isAdmin()
                    ? app(\App\Services\AdminPendingApprovalService::class)->counts()
                    : ['total' => 0];
                $adminPendingUrl = Auth::user()?->isAdmin()
                    ? app(\App\Services\AdminPendingApprovalService::class)->primaryActionUrl($adminPending)
                    : route('admin.dashboard');
            @endphp

            @if (($adminPending['total'] ?? 0) > 0)
                <a href="{{ $adminPendingUrl }}"
                   title="{{ $adminPending['total'] }} việc cần xử lý"
                   class="relative inline-flex rounded-xl border border-white/70 bg-white/70 p-2.5 text-slate-500 shadow-sm transition hover:bg-violet-50 hover:text-violet-700 sm:rounded-2xl">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                        {{ $adminPending['total'] > 9 ? '9+' : $adminPending['total'] }}
                    </span>
                </a>
            @endif

            @include('admin.partials.notification-dropdown')
            <span class="hidden items-center gap-1.5 rounded-full bg-gradient-to-r from-violet-100 to-indigo-100 px-3 py-1.5 text-xs font-semibold text-violet-700 md:inline-flex">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-violet-500"></span>
                {{ Auth::user()->role?->label() ?? 'Admin' }}
            </span>

            <div class="flex items-center gap-1.5 pl-1 sm:gap-2 sm:border-l sm:border-slate-200/80 sm:pl-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 text-sm font-bold text-white shadow-md shadow-violet-500/20 ring-2 ring-white sm:h-10 sm:w-10 sm:rounded-2xl">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="rounded-xl px-3 py-2 text-xs font-semibold text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                        Đăng xuất
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}" class="sm:hidden">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                            aria-label="Đăng xuất">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('success') || session('error'))
        <div class="px-4 pb-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 rounded-2xl border {{ session('error') ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }} px-4 py-3 text-sm font-semibold shadow-sm">
                @if(session('error'))
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                @else
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                @endif
                <span>{{ session('error') ?? session('success') }}</span>
            </div>
        </div>
    @endif
</header>
