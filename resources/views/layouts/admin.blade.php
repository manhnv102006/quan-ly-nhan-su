<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - {{ config('app.name', 'Quản lý nhân sự') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="admin-body admin-shell" x-data="{ sidebarOpen: false }">
    @include('partials.page-loader')

    <div class="min-h-screen flex relative">
        {{-- Decorative blobs --}}
        <div class="pointer-events-none fixed top-20 left-[280px] w-72 h-72 bg-violet-400/10 rounded-full blur-3xl"></div>
        <div class="pointer-events-none fixed bottom-10 right-10 w-96 h-96 bg-cyan-400/10 rounded-full blur-3xl"></div>

        {{-- Sidebar --}}
        <aside
            class="admin-sidebar fixed inset-y-0 left-0 z-50 w-[270px] h-screen transform transition-transform duration-300 ease-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen flex flex-col shrink-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            {{-- Brand --}}
            <div class="flex items-center gap-3 px-5 h-[72px]">
                <div class="relative">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <x-application-logo class="w-6 h-6 fill-current text-white" />
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>
                </div>
                <div>
                    <p class="font-extrabold text-slate-800 tracking-tight">PeopleHub</p>
                    <p class="text-[11px] font-medium text-violet-600 uppercase tracking-wider">Admin Panel</p>
                </div>
            </div>

            <div class="px-4 mb-2">
                <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Menu chính</p>
            </div>

            <nav class="flex-1 px-3 space-y-0.5 overflow-y-auto">
                @include('admin.partials.sidebar-menu')
            </nav>

            {{-- Sidebar footer --}}
            <div class="p-4 m-3 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 text-white">
                <p class="text-xs font-semibold opacity-90">Cần hỗ trợ?</p>
                <p class="text-[11px] mt-1 text-violet-200 leading-relaxed">Liên hệ IT để được hỗ trợ hệ thống HR.</p>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen relative">
            @include('admin.partials.header')

            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
