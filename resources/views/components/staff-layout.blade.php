@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'role' => 'manager',
    'navigation' => [],
])

@php
    $user = Auth::user();
    $isManager = $role === 'manager';
    $theme = $isManager
        ? [
            'shell' => 'staff-shell-manager',
            'brand' => 'from-emerald-500 via-teal-500 to-cyan-600',
            'avatar' => 'from-emerald-500 via-teal-500 to-cyan-500',
            'badge' => 'from-emerald-100 to-cyan-100 text-emerald-700',
            'badgeDot' => 'bg-emerald-500',
            'activeMenu' => 'staff-menu-item-active-manager',
            'accentText' => 'text-emerald-600',
            'ghost' => 'hover:bg-emerald-50 hover:text-emerald-700',
            'searchFocus' => 'focus:ring-emerald-500/30',
            'support' => 'from-emerald-600 to-cyan-700',
            'glowOne' => 'bg-emerald-400/15',
            'glowTwo' => 'bg-cyan-400/15',
            'roleLabel' => 'Manager',
            'eyebrow' => 'Khong gian dieu hanh',
            'helper' => 'Theo doi doi ngu, phe duyet va tien do cua phong ban.',
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
            'eyebrow' => 'Khong gian ca nhan',
            'helper' => 'Cham cong, KPI, bang luong va thong bao moi deu tap trung tai day.',
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

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="staff-body staff-shell {{ $theme['shell'] }}" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex relative">
        <div class="pointer-events-none fixed top-20 left-[280px] h-72 w-72 rounded-full {{ $theme['glowOne'] }} blur-3xl"></div>
        <div class="pointer-events-none fixed bottom-10 right-10 h-96 w-96 rounded-full {{ $theme['glowTwo'] }} blur-3xl"></div>

        <aside
            class="staff-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[280px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-[74px] items-center gap-3 px-5">
                <div class="relative">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $theme['brand'] }} shadow-lg shadow-slate-900/10">
                        <x-application-logo class="h-6 w-6 fill-current text-white" />
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $theme['badgeDot'] }}"></span>
                </div>
                <div>
                    <p class="font-extrabold tracking-tight text-slate-800">PeopleHub</p>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] {{ $theme['accentText'] }}">{{ $theme['roleLabel'] }} Panel</p>
                </div>
            </div>

            <div class="mb-2 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Dieu huong</p>
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
                <p class="text-xs font-semibold opacity-90">Xin chao, {{ $firstName }}</p>
                <p class="mt-1 text-[11px] leading-relaxed text-white/80">{{ $theme['helper'] }}</p>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    Tai khoan dang hoat dong
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
            <header class="staff-header sticky top-0 z-30">
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
                                    placeholder="Tim kiem nhanh..."
                                    class="w-full rounded-xl border-0 bg-slate-100/90 py-2.5 pl-10 pr-4 text-sm text-slate-600 placeholder:text-slate-400 focus:bg-white {{ $theme['searchFocus'] }}"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="hidden rounded-2xl border border-white/70 bg-white/75 px-3 py-2 text-right shadow-sm shadow-slate-200/50 sm:block">
                            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Hom nay</p>
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
                            Ho so
                        </a>

                        <div class="flex items-center gap-2 border-l border-slate-200/80 pl-2 sm:pl-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $theme['avatar'] }} text-sm font-bold text-white shadow-md shadow-slate-900/10">
                                {{ $initial }}
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                                @csrf
                                <button type="submit" class="rounded-xl px-3 py-2 text-xs font-medium text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                                    Dang xuat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 pb-10 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
