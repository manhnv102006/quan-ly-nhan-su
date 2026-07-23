@php
    $user = Auth::user();
    $firstName = collect(explode(' ', trim($user?->name ?? 'User')))->filter()->first() ?? ($user?->name ?? 'User');
    $initial = strtoupper(mb_substr($user?->name ?? 'U', 0, 1));
    $navigation = \App\Support\AccountantNavigation::items();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Kế toán · {{ config('app.name', 'Quản lý nhân sự') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
    @stack('head')
</head>
<body class="accountant-body accountant-shell" x-data="{ sidebarOpen: false }">
    @include('partials.page-loader')

    <div class="relative flex min-h-screen">
        <div class="pointer-events-none fixed left-[280px] top-16 h-80 w-80 rounded-full bg-amber-400/15 blur-3xl"></div>
        <div class="pointer-events-none fixed right-8 top-32 h-72 w-72 rounded-full bg-indigo-400/12 blur-3xl"></div>
        <div class="pointer-events-none fixed bottom-0 left-1/3 h-80 w-80 rounded-full bg-orange-300/10 blur-3xl"></div>

        <aside
            class="accountant-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[252px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="border-b border-amber-100/80 px-4 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                        <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 shadow-md shadow-amber-200/50">
                            <x-application-logo class="h-full w-full object-contain p-1.5" />
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-amber-500"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-extrabold tracking-tight text-slate-900">Quản lý nhân sự</p>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-amber-700">Kế toán</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-2.5 pb-2 pt-3">
                @include('accountant.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>

            <div class="m-2.5 rounded-xl bg-gradient-to-br from-slate-950 via-amber-950 to-indigo-950 p-3 text-white shadow-lg shadow-amber-900/20">
                <p class="text-xs font-bold opacity-95">Xin chào, {{ $firstName }}</p>
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-semibold">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-400"></span>
                    Đang hoạt động
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
            @include('accountant.partials.header', ['title' => $title ?? 'Dashboard', 'subtitle' => $subtitle ?? null])
            <main class="relative z-0 flex-1 p-4 pb-10 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
