<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin' }} - {{ config('app.name', 'Quản lý nhân sự') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white transform transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-3 px-6 h-16 border-b border-blue-700/50">
                <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
                    <x-application-logo class="w-6 h-6 fill-current text-white" />
                </div>
                <div>
                    <p class="font-bold text-sm leading-tight">HR System</p>
                    <p class="text-xs text-blue-200">Quản trị hệ thống</p>
                </div>
            </div>

            <nav class="px-3 py-4 space-y-1 overflow-y-auto max-h-[calc(100vh-4rem)]">
                @include('admin.partials.sidebar-menu')
            </nav>
        </aside>

        {{-- Overlay mobile --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            style="display: none;"
        ></div>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            @include('admin.partials.header')

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
