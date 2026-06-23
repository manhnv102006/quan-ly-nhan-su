<x-admin-layout title="Dashboard">
    @php
        $gradients = [
            'from-violet-500 to-indigo-600',
            'from-cyan-500 to-blue-600',
            'from-fuchsia-500 to-pink-600',
            'from-emerald-500 to-teal-600',
            'from-amber-500 to-orange-600',
            'from-rose-500 to-red-600',
            'from-sky-500 to-indigo-600',
            'from-lime-500 to-green-600',
        ];
    @endphp

    {{-- Hero welcome --}}
    <div class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-indigo-600 to-cyan-500 p-6 sm:p-8 text-white shadow-xl shadow-violet-500/20">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-400/20 rounded-full translate-y-1/2 -translate-x-1/4 blur-2xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-xs font-semibold backdrop-blur mb-3">
                    ✨ Hôm nay là ngày tuyệt vời
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Hey, {{ explode(' ', Auth::user()->name)[0] }}!
                </h2>
                <p class="mt-2 text-violet-100 text-sm sm:text-base max-w-lg">
                    Đây là bảng điều khiển quản lý nhân sự — mọi thứ bạn cần đều ở đây.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-violet-200 font-semibold">Hôm nay</p>
                        <p class="text-sm font-bold">{{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/15 backdrop-blur border border-white/20">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">🚀</div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-violet-200 font-semibold">Trạng thái</p>
                        <p class="text-sm font-bold">Online</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats bento grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        @foreach ($stats as $index => $stat)
            <a href="{{ route($stat['route']) }}" class="admin-stat-card group block">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $gradients[$index % count($gradients)] }} flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-violet-500 transition">Xem →</span>
                </div>
                <p class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ number_format($stat['value']) }}</p>
                <p class="mt-1 text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Quick actions --}}
        <div class="xl:col-span-2 admin-card p-6 sm:p-7">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Truy cập nhanh</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Các tác vụ thường dùng nhất</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @php
                    $actions = [
                        ['route' => 'admin.employees', 'label' => 'Nhân viên', 'emoji' => '👥', 'color' => 'from-violet-500 to-purple-600'],
                        ['route' => 'admin.attendances', 'label' => 'Chấm công', 'emoji' => '⏰', 'color' => 'from-cyan-500 to-blue-600'],
                        ['route' => 'admin.payrolls', 'label' => 'Lương', 'emoji' => '💰', 'color' => 'from-emerald-500 to-teal-600'],
                        ['route' => 'admin.kpis.index', 'label' => 'KPI', 'emoji' => '📊', 'color' => 'from-amber-500 to-orange-600'],
                        ['route' => 'admin.contracts', 'label' => 'Hợp đồng', 'emoji' => '📄', 'color' => 'from-rose-500 to-pink-600'],
                        ['route' => 'admin.recruitment', 'label' => 'Tuyển dụng', 'emoji' => '🎯', 'color' => 'from-indigo-500 to-violet-600'],
                    ];
                @endphp

                @foreach ($actions as $action)
                    <a href="{{ route($action['route']) }}" class="group relative overflow-hidden rounded-2xl p-4 bg-slate-50 hover:bg-white border border-slate-100 hover:border-violet-200 hover:shadow-md hover:shadow-violet-100 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br {{ $action['color'] }} opacity-0 group-hover:opacity-5 transition-opacity"></div>
                        <div class="relative">
                            <span class="text-2xl">{{ $action['emoji'] }}</span>
                            <p class="mt-2 text-sm font-bold text-slate-700 group-hover:text-violet-700 transition">{{ $action['label'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Activity panel --}}
        <div class="admin-card p-6 sm:p-7">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Hoạt động</h3>
            <p class="text-xs text-slate-500 mb-6">Tình trạng hệ thống</p>

            <div class="space-y-4">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500 text-white text-sm">✓</span>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Hệ thống ổn định</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Tất cả dịch vụ hoạt động bình thường</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-violet-50 border border-violet-100">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-500 text-white text-sm">★</span>
                    <div>
                        <p class="text-sm font-semibold text-violet-800">Quyền Admin</p>
                        <p class="text-xs text-violet-600 mt-0.5">Toàn quyền quản trị hệ thống</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500 text-white text-sm">📢</span>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Tuyển dụng</p>
                        <p class="text-xs text-amber-600 mt-0.5">{{ $recentJobs->count() }} tin đang mở</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Tip</p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Dùng menu bên trái để điều hướng nhanh giữa các module quản lý.
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
