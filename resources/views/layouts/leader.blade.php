@php
    $user = Auth::user();
    $firstName = collect(explode(' ', trim($user?->name ?? 'User')))->filter()->first() ?? ($user?->name ?? 'User');
    $navigation = \App\Support\LeaderNavigation::items();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Trưởng nhóm · {{ config('app.name', 'Quản lý nhân sự') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
    @stack('head')
</head>
<body class="leader-body leader-shell" x-data="{ sidebarOpen: false }">
    @include('partials.page-loader')

    <div class="relative flex min-h-screen">
        <div class="pointer-events-none fixed left-[280px] top-16 h-80 w-80 rounded-full bg-violet-400/15 blur-3xl"></div>
        <div class="pointer-events-none fixed right-8 top-32 h-72 w-72 rounded-full bg-fuchsia-400/12 blur-3xl"></div>

        <aside
            class="leader-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[286px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="border-b border-violet-100/80 px-5 py-6">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 shadow-md shadow-violet-200/50">
                            <x-application-logo class="h-full w-full object-contain p-1.5" />
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-4 w-4 rounded-full border-2 border-white bg-violet-500"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-extrabold tracking-tight text-slate-900">Quản lý nhân sự</p>
                        <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.2em] text-violet-700">Trưởng nhóm</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 mt-5 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Menu chính</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-2">
                @include('leader.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>

            <div class="m-3 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-violet-950 to-fuchsia-900 p-4 text-white shadow-lg shadow-violet-900/20">
                <p class="text-xs font-bold opacity-95">Xin chào, {{ $firstName }}</p>
                <p class="mt-1 text-[11px] leading-relaxed text-violet-100/85">Theo dõi nhân viên, KPI và công việc nhóm.</p>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-violet-400"></span>
                    Leader · Hoạt động
                </div>
            </div>
        </aside>

        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col">
            @include('leader.partials.header', ['title' => $title ?? 'Dashboard', 'subtitle' => $subtitle ?? null])
            <main class="relative z-0 flex-1 p-4 pb-10 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
