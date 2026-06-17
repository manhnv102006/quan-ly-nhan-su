<header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        <div class="flex items-center gap-3">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div>
                <h1 class="text-lg font-semibold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
                <p class="text-xs text-slate-500 hidden sm:block">Xin chào, {{ Auth::user()->name }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Admin
            </div>

            <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-500 hover:text-red-600 transition px-2 py-1 rounded-lg hover:bg-red-50">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
