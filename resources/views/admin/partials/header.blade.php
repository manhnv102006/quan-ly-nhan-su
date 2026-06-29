<header class="admin-header sticky top-0 z-30">
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
            {{-- Notification --}}
            <button class="relative p-2.5 rounded-xl text-slate-500 hover:bg-violet-50 hover:text-violet-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
            </button>

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
