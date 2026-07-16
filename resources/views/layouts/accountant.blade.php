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
            class="accountant-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[286px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="border-b border-amber-100/80 px-5 py-6">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 shadow-md shadow-amber-200/50">
                            <x-application-logo class="h-full w-full object-contain p-1.5" />
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-4 w-4 rounded-full border-2 border-white bg-amber-500"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-extrabold tracking-tight text-slate-900">Quản lý nhân sự</p>
                        <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-700">Trang Kế toán</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 mt-5 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Tài chính &amp; nhân sự</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-2">
                @include('accountant.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>

            <div class="m-3 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-amber-950 to-indigo-950 p-4 text-white shadow-lg shadow-amber-900/20">
                <div class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/15">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-xs font-bold opacity-95">Xin chào, {{ $firstName }}</p>
                <p class="mt-1 text-[11px] leading-relaxed text-amber-100/85">Quản lý lương, bảo hiểm, thuế và báo cáo tài chính.</p>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-400"></span>
                    Kế toán · Hoạt động
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
