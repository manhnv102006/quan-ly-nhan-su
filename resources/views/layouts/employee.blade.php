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

    <div class="relative flex min-h-screen overflow-x-hidden">
        {{-- Decorative blobs — Sky/Blue (khác Admin Violet & Manager Teal) --}}
        <div class="pointer-events-none fixed left-[280px] top-16 hidden h-80 w-80 rounded-full bg-sky-400/15 blur-3xl sm:block"></div>
        <div class="pointer-events-none fixed right-8 top-32 hidden h-72 w-72 rounded-full bg-blue-400/15 blur-3xl sm:block"></div>
        <div class="pointer-events-none fixed bottom-0 left-1/2 hidden h-80 w-80 -translate-x-1/2 rounded-full bg-indigo-300/10 blur-3xl sm:block"></div>

        {{-- Sidebar --}}
        <aside
            class="employee-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[min(88vw,320px)] shrink-0 transform flex-col transition-transform duration-300 ease-out lg:w-[286px] lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="border-b border-white/70 px-4 py-4 sm:px-5 sm:py-6">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white bg-white shadow-md shadow-sky-200/50 sm:h-14 sm:w-14">
                            <x-application-logo class="h-full w-full object-contain p-1.5" />
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-4 w-4 rounded-full border-2 border-white bg-sky-500"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-base font-extrabold leading-tight tracking-tight text-slate-900 sm:text-lg">Quản lý nhân sự</p>
                        <p class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.2em] text-sky-600 sm:text-[11px]">Trang nhân viên</p>
                    </div>
                    <button type="button"
                            @click="sidebarOpen = false"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                            aria-label="Đóng menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mb-2 mt-5 px-4">
                <p class="px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">Menu chính</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-2">
                @include('employee.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>

            <div class="m-3 hidden overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-sky-900 to-blue-800 p-4 text-white shadow-lg shadow-sky-500/20 sm:block">
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
            class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col lg:pl-[286px]">
            @include('employee.partials.header', [
                'title' => $title ?? 'Dashboard',
                'subtitle' => $subtitle ?? null,
                'employeePendingActions' => $employeePendingActions,
            ])

            <main class="relative z-0 min-w-0 flex-1 p-3 pb-28 sm:p-6 sm:pb-28 lg:p-8 lg:pb-10">
                {{ $slot }}
            </main>
        </div>

        {{-- Mobile bottom navigation --}}
        @php
            $bottomNavItems = [
                [
                    'label' => 'Bảng lương',
                    'href' => route('employee.payrolls.index'),
                    'active' => request()->routeIs('employee.payrolls.*'),
                    'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label' => 'Tăng ca',
                    'href' => route('employee.overtime-requests'),
                    'active' => request()->routeIs('employee.overtime-requests*'),
                    'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
                ],
                [
                    'label' => 'Thông báo',
                    'href' => route('employee.notifications.index'),
                    'active' => request()->routeIs('employee.notifications*'),
                    'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75v-.7V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
                ],
                [
                    'label' => 'Hồ sơ',
                    'href' => route('profile.edit'),
                    'active' => request()->routeIs('profile.*'),
                    'icon' => 'M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                ],
            ];
            $checkInActive = request()->routeIs('attendance.*');
        @endphp

        <nav
            class="fixed inset-x-0 bottom-0 z-30 border-t border-white/80 bg-white/90 px-2 pb-2 pt-2 shadow-[0_-16px_40px_-24px_rgba(15,23,42,0.45)] backdrop-blur-2xl transition-transform duration-300 lg:hidden"
            :class="sidebarOpen ? 'translate-y-full' : 'translate-y-0'"
            style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
        >
            <div class="mx-auto grid max-w-md grid-cols-5 items-end gap-1 text-center">
                <a href="{{ $bottomNavItems[0]['href'] }}"
                   class="flex flex-col items-center justify-center gap-1 rounded-2xl px-1.5 py-1.5 text-center text-[10px] font-bold transition {{ $bottomNavItems[0]['active'] ? 'text-sky-600' : 'text-slate-500 hover:text-sky-600' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $bottomNavItems[0]['active'] ? 'bg-sky-100 text-sky-600' : 'bg-slate-50 text-slate-500' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $bottomNavItems[0]['icon'] }}" />
                        </svg>
                    </span>
                    <span class="block w-full leading-tight">Lương</span>
                </a>

                <a href="{{ $bottomNavItems[1]['href'] }}"
                   class="flex flex-col items-center justify-center gap-1 rounded-2xl px-1.5 py-1.5 text-center text-[10px] font-bold transition {{ $bottomNavItems[1]['active'] ? 'text-sky-600' : 'text-slate-500 hover:text-sky-600' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $bottomNavItems[1]['active'] ? 'bg-sky-100 text-sky-600' : 'bg-slate-50 text-slate-500' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $bottomNavItems[1]['icon'] }}" />
                        </svg>
                    </span>
                    <span class="block w-full leading-tight">Tăng ca</span>
                </a>

                <a href="{{ route('attendance.index') }}"
                   class="relative -mt-8 flex flex-col items-center justify-center gap-1 text-center text-[10px] font-black {{ $checkInActive ? 'text-sky-700' : 'text-slate-700' }}">
                    <span class="flex h-16 w-16 items-center justify-center rounded-[1.4rem] bg-gradient-to-br from-sky-500 via-blue-500 to-indigo-500 text-white shadow-xl shadow-sky-500/30 ring-4 ring-white">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5H5.25A.75.75 0 0 0 4.5 5.25V7.5m12-3h2.25a.75.75 0 0 1 .75.75V7.5m0 9v2.25a.75.75 0 0 1-.75.75H16.5m-9 0H5.25a.75.75 0 0 1-.75-.75V16.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10.25h.01M15 10.25h.01M9.75 15.25c1.25.9 3.25.9 4.5 0" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12.25c0-2.07 1.68-3.75 3.75-3.75s3.75 1.68 3.75 3.75S14.07 16 12 16s-3.75-1.68-3.75-3.75Z" />
                        </svg>
                    </span>
                    <span class="block rounded-full bg-white px-2 py-0.5 leading-tight shadow-sm">Quét mặt</span>
                </a>

                <a href="{{ $bottomNavItems[2]['href'] }}"
                   class="relative flex flex-col items-center justify-center gap-1 rounded-2xl px-1.5 py-1.5 text-center text-[10px] font-bold transition {{ $bottomNavItems[2]['active'] ? 'text-sky-600' : 'text-slate-500 hover:text-sky-600' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $bottomNavItems[2]['active'] ? 'bg-sky-100 text-sky-600' : 'bg-slate-50 text-slate-500' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $bottomNavItems[2]['icon'] }}" />
                        </svg>
                    </span>
                    <span class="block w-full leading-tight">Thông báo</span>
                </a>

                <a href="{{ $bottomNavItems[3]['href'] }}"
                   class="flex flex-col items-center justify-center gap-1 rounded-2xl px-1.5 py-1.5 text-center text-[10px] font-bold transition {{ $bottomNavItems[3]['active'] ? 'text-sky-600' : 'text-slate-500 hover:text-sky-600' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $bottomNavItems[3]['active'] ? 'bg-sky-100 text-sky-600' : 'bg-slate-50 text-slate-500' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $bottomNavItems[3]['icon'] }}" />
                        </svg>
                    </span>
                    <span class="block w-full leading-tight">Hồ sơ</span>
                </a>
            </div>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
