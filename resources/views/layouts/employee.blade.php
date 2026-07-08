@php
    $user = Auth::user();
    $firstName = collect(explode(' ', trim($user?->name ?? 'User')))->filter()->first() ?? ($user?->name ?? 'User');

    $navigation = \App\Support\EmployeeNavigation::items();
    $employeePendingActions = ['kpis' => 0, 'total' => 0];

    if ($user) {
        $employeePendingActions = app(\App\Services\EmployeePendingActionService::class)->countsForUser($user);
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Quản lý nhân sự') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="employee-body employee-shell" x-data="{ sidebarOpen: false }">
    @include('partials.page-loader')

    <div class="relative flex min-h-screen">
        {{-- Decorative blobs — Sky/Blue (khác Admin Violet & Manager Teal) --}}
        <div class="pointer-events-none fixed left-[280px] top-16 h-80 w-80 rounded-full bg-sky-400/15 blur-3xl"></div>
        <div class="pointer-events-none fixed right-8 top-32 h-72 w-72 rounded-full bg-blue-400/15 blur-3xl"></div>
        <div class="pointer-events-none fixed bottom-0 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-indigo-300/10 blur-3xl"></div>

        {{-- Sidebar --}}
        <aside
            class="employee-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[286px] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="border-b border-white/70 px-5 py-6">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-white bg-white shadow-md shadow-sky-200/50">
                            <x-application-logo class="h-full w-full object-contain p-1.5" />
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-4 w-4 rounded-full border-2 border-white bg-sky-500"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-lg font-extrabold leading-tight tracking-tight text-slate-900">Quản lý nhân sự</p>
                        <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.2em] text-sky-600">Trang nhân viên</p>
                    </div>
                </div>
            </div>

            <div class="mb-2 mt-5 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Menu chính</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-2">
                @include('employee.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>

            <div class="m-3 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-sky-900 to-blue-800 p-4 text-white shadow-lg shadow-sky-500/20">
                <div class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/15">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.178-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 17.25h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <p class="text-xs font-bold opacity-95">Xin chào, {{ $firstName }}</p>
                <p class="mt-1 text-[11px] leading-relaxed text-sky-100/85">Chấm công, KPI, lương và thông báo tại đây.</p>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold backdrop-blur">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
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
            class="fixed inset-0 z-40 bg-slate-900/20 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col">
            @include('employee.partials.header', [
                'title' => $title ?? 'Dashboard',
                'subtitle' => $subtitle ?? null,
                'employeePendingActions' => $employeePendingActions,
            ])

            <main class="relative z-0 flex-1 p-4 pb-10 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
