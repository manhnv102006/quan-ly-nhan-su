@php
    $user = Auth::user();
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
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">
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
        <aside
            class="employee-sidebar fixed inset-y-0 left-0 z-50 flex h-screen w-[260px] shrink-0 transform flex-col transition-transform duration-200 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-sky-100 bg-gradient-to-br from-sky-50 to-blue-50 shadow-sm">
                        <x-application-logo class="h-full w-full object-contain p-1.5" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-900">Quản lý nhân sự</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-sky-600">Nhân viên</p>
                    </div>
                </div>
                <button type="button"
                        @click="sidebarOpen = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden"
                        aria-label="Đóng menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-2 py-3">
                @include('employee.partials.sidebar-menu', ['navigation' => $navigation])
            </nav>
        </aside>

        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/30 lg:hidden"
            style="display: none;"
        ></div>

        <div class="relative flex min-h-screen min-w-0 flex-1 flex-col lg:pl-[260px]">
            @include('employee.partials.header', [
                'title' => $title ?? 'Dashboard',
                'subtitle' => $subtitle ?? null,
                'employeePendingActions' => $employeePendingActions,
            ])

            <main class="relative z-0 min-w-0 flex-1 p-4 pb-24 lg:p-6 lg:pb-8">
                {{ $slot }}
            </main>
        </div>

        @php
            $mobileNav = [
                ['label' => 'Chấm công', 'href' => route('attendance.index'), 'active' => request()->routeIs('attendance.*')],
                ['label' => 'Lương', 'href' => route('employee.payrolls.index'), 'active' => request()->routeIs('employee.payrolls.*')],
                ['label' => 'KPI', 'href' => route('employee.kpis.index'), 'active' => request()->routeIs('employee.kpis.*')],
                ['label' => 'TB', 'href' => route('employee.notifications.index'), 'active' => request()->routeIs('employee.notifications*')],
                ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
            ];
        @endphp

        <nav
            class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white lg:hidden"
            :class="sidebarOpen ? 'translate-y-full' : 'translate-y-0'"
            style="padding-bottom: env(safe-area-inset-bottom);"
        >
            <div class="grid grid-cols-5">
                @foreach ($mobileNav as $item)
                    <a href="{{ $item['href'] }}"
                       class="flex flex-col items-center gap-0.5 py-2.5 text-[10px] font-medium {{ $item['active'] ? 'text-slate-900' : 'text-slate-500' }}">
                        <span class="h-1 w-1 rounded-full {{ $item['active'] ? 'bg-slate-900' : 'bg-transparent' }}"></span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
