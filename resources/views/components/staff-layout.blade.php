@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'role' => 'manager',
    'navigation' => [],
    'bootstrap' => false,
])

@php
    $user = Auth::user();
    $isManager = $role === 'manager';
    $theme = $isManager
        ? [
            'shell' => 'staff-shell-manager',
            'brand' => 'from-violet-600 via-indigo-600 to-blue-600',
            'avatar' => 'from-violet-500 via-indigo-500 to-blue-500',
            'badge' => 'from-violet-100 to-indigo-100 text-violet-700',
            'badgeDot' => 'bg-violet-500',
            'activeMenu' => 'staff-menu-item-active-manager',
            'accentText' => 'text-violet-600',
            'ghost' => 'hover:bg-violet-50 hover:text-violet-700',
            'searchFocus' => 'focus:ring-violet-500/30',
            'support' => 'from-slate-800 via-violet-900 to-indigo-900',
            'glowOne' => 'bg-violet-400/15',
            'glowTwo' => 'bg-indigo-400/12',
            'roleLabel' => 'Manager',
            'eyebrow' => 'Không gian điều hành',
            'helper' => 'Theo dõi đội ngũ, phê duyệt và tiến độ của phòng ban.',
        ]
        : [
            'shell' => 'staff-shell-employee',
            'brand' => 'from-sky-500 via-blue-500 to-indigo-600',
            'avatar' => 'from-sky-500 via-blue-500 to-indigo-500',
            'badge' => 'from-sky-100 to-indigo-100 text-sky-700',
            'badgeDot' => 'bg-sky-500',
            'activeMenu' => 'staff-menu-item-active-employee',
            'accentText' => 'text-sky-600',
            'ghost' => 'hover:bg-sky-50 hover:text-sky-700',
            'searchFocus' => 'focus:ring-sky-500/30',
            'support' => 'from-sky-600 to-indigo-700',
            'glowOne' => 'bg-sky-400/15',
            'glowTwo' => 'bg-indigo-400/15',
            'roleLabel' => 'Employee',
            'eyebrow' => 'Không gian cá nhân',
            'helper' => 'Chấm công, KPI, bảng lương và thông báo mới đều tập trung tại đây.',
        ];
    $firstName = collect(explode(' ', trim($user?->name ?? 'User')))->filter()->first() ?? ($user?->name ?? 'User');
    $initial = strtoupper(mb_substr($user?->name ?? 'U', 0, 1));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'Quan ly nhan su') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($bootstrap)
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="staff-body staff-shell {{ $theme['shell'] }}" x-data="{ sidebarOpen: false }">
    @include('partials.page-loader')

    <div class="min-h-screen flex relative">
        <div class="pointer-events-none fixed top-20 left-[280px] h-72 w-72 rounded-full {{ $theme['glowOne'] }} blur-3xl"></div>
        <div class="pointer-events-none fixed bottom-10 right-10 h-96 w-96 rounded-full {{ $theme['glowTwo'] }} blur-3xl"></div>

        <aside
            class="staff-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[280px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex flex-col items-center justify-center gap-2 px-5 py-6 border-b border-slate-100/50">
                <div class="relative">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl overflow-hidden bg-white shadow-md border border-slate-100">
                        <x-application-logo class="h-full w-full object-contain p-1.5" />
                    </div>
                    <span class="absolute bottom-1 right-1 h-4 w-4 rounded-full border-2 border-white {{ $theme['badgeDot'] }}"></span>
                </div>
                <div class="text-center mt-1">
                    <p class="font-extrabold tracking-tight text-slate-800 text-xl leading-tight">Quản lý nhân sự</p>
                    <p class="text-[11px] font-semibold uppercase tracking-widest {{ $theme['accentText'] }} mt-0.5">Trang {{ $theme['roleLabel'] }}</p>
                </div>
            </div>

            <div class="mb-2 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Điều hướng</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3">
                @foreach ($navigation as $item)
                    @php
                        $isActive = $item['active'] ?? (isset($item['route']) ? request()->routeIs($item['route']) : false);
                    @endphp
                    <a
                        href="{{ $item['href'] }}"
                        @if (! empty($item['target'])) target="{{ $item['target'] }}" @endif
                        class="staff-menu-item {{ $isActive ? $theme['activeMenu'] : 'staff-menu-item-inactive' }}"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-white/20 text-white' : 'bg-white text-slate-500 shadow-sm shadow-slate-200/60' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{ $item['label'] }}</span>
                            @if (! empty($item['note']))
                                <span class="block truncate text-[11px] {{ $isActive ? 'text-white/70' : 'text-slate-400' }}">{{ $item['note'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="m-3 rounded-3xl bg-gradient-to-br {{ $theme['support'] }} p-4 text-white">
                <p class="text-xs font-semibold opacity-90">Xin chào, {{ $firstName }}</p>
                <p class="mt-1 text-[11px] leading-relaxed text-white/80">{{ $theme['helper'] }}</p>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    Tài khoản đang hoạt động
                </div>
            </div>
        </aside>

        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/25 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col">
            <header class="staff-header sticky top-0 z-50 overflow-visible">
                <div class="flex h-[74px] items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-4">
                        <button
                            @click="sidebarOpen = !sidebarOpen"
                            class="rounded-xl p-2.5 text-slate-500 transition lg:hidden {{ $theme['ghost'] }}"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">{{ $theme['eyebrow'] }}</p>
                            <h1 class="truncate text-xl font-bold tracking-tight text-slate-800">{{ $title }}</h1>
                            @if ($subtitle)
                                <p class="hidden text-xs text-slate-500 sm:block">{{ $subtitle }}</p>
                            @endif
                        </div>

                        <div class="ml-4 hidden max-w-md flex-1 md:flex">
                            <div class="relative w-full">
                                <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                <input
                                    type="text"
                                    placeholder="Tìm kiếm nhanh..."
                                    class="w-full rounded-xl border-0 bg-slate-100/90 py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:bg-white {{ $theme['searchFocus'] }}"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @include('admin.partials.notification-dropdown')

                        <div class="hidden rounded-2xl border border-white/70 bg-white/75 px-3 py-2 text-right shadow-sm shadow-slate-200/50 sm:block">
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Hôm nay</p>
                            <p class="text-sm font-semibold text-slate-700">{{ now()->format('d/m/Y') }}</p>
                        </div>

                        <span class="hidden items-center gap-2 rounded-full bg-gradient-to-r px-3 py-1.5 text-xs font-semibold sm:inline-flex {{ $theme['badge'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $theme['badgeDot'] }}"></span>
                            {{ $theme['roleLabel'] }}
                        </span>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="hidden rounded-xl px-3 py-2 text-xs font-medium text-slate-500 transition sm:inline-flex {{ $theme['ghost'] }}"
                        >
                            Hồ sơ
                        </a>

                        <div class="flex items-center gap-2 border-l border-slate-200/80 pl-2 sm:pl-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $theme['avatar'] }} text-sm font-bold text-white shadow-md shadow-slate-900/10">
                                {{ $initial }}
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                                @csrf
                                <button type="submit" class="rounded-xl px-3 py-2 text-xs font-medium text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative z-0 flex-1 p-4 pb-10 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @if ($bootstrap)
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @endif
    @stack('scripts')
</body>
</html>
