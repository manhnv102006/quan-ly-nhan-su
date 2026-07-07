<header class="admin-header sticky top-0 z-50 overflow-visible">
    <div class="flex items-center justify-between h-[72px] px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 flex-1 min-w-0">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2.5 rounded-xl text-slate-500 hover:bg-violet-50 hover:text-violet-600 transition"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="min-w-0">
                <h1 class="text-xl font-bold text-slate-800 tracking-tight truncate">{{ $title ?? 'Dashboard' }}</h1>
                <p class="text-xs text-slate-500 hidden sm:block">
                    Xin chào <span class="font-semibold text-violet-600">{{ Auth::user()->name }}</span> 👋
                </p>
            </div>

            {{-- Search (decorative) --}}
            <div class="hidden md:flex flex-1 max-w-md ml-4">
                <div class="relative w-full">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        type="text"
                        placeholder="Tìm kiếm nhanh..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-100/80 border-0 rounded-xl text-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-violet-500/30 focus:bg-white transition"
                    >
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
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
                   class="relative inline-flex rounded-xl p-2.5 text-slate-500 transition hover:bg-violet-50 hover:text-violet-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                        {{ $adminPending['total'] > 9 ? '9+' : $adminPending['total'] }}
                    </span>
                </a>
            @endif

            @include('admin.partials.notification-dropdown')
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-violet-100 to-indigo-100 text-violet-700">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
                {{ Auth::user()->role?->label() ?? 'Admin' }}
            </span>

            <div class="flex items-center gap-2 pl-2 sm:pl-3 sm:border-l border-slate-200/80">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-violet-500/20">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-slate-500 hover:text-rose-600 px-3 py-2 rounded-xl hover:bg-rose-50 transition">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('success') || session('error'))
        <div class="px-6 sm:px-8 lg:px-10 py-3">
            <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                {{ session('error') ?? session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
</header>
