@php


    $menuItems = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['route' => 'admin.accounts', 'label' => 'Quản lý tài khoản', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['route' => 'admin.departments', 'label' => 'Quản lý phòng ban', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z'],
        ['route' => 'admin.positions', 'label' => 'Quản lý chức vụ', 'icon' => 'M20.25 14.15v4.25c0 .414-.336.75-.75.75h-4.5a.75.75 0 01-.75-.75v-4.25m0 0h4.125c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9m9 8.25H18M4.5 9.75h7.5m-7.5 3H9'],
        ['route' => 'admin.employees', 'label' => 'Quản lý nhân viên', 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
        [
            'route' => 'admin.attendances',
            'label' => 'Quản lý chấm công',
            'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            'children' => [
                ['route' => 'admin.shifts.index', 'label' => 'Quản lý ca'],
                ['route' => 'admin.leave-requests.index', 'label' => 'Duyệt nghỉ phép'],
                ['route' => 'admin.overtime-requests.index', 'label' => 'Duyệt tăng ca'],

                ['route' => 'admin.attendance-reports.index', 'label' => 'Báo cáo chấm công']
            ]
        ],
        [
            'route' => 'admin.kpis.index',
            'label' => 'Quản lý KPI',
            'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            'children' => [
                ['route' => 'admin.kpi-assignments.index', 'label' => 'Giao KPI'],
            ]
        ]
    ];


    $user = Auth::user();

    if ($user?->isAdmin()) {
        $menuItems = [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
            ['route' => 'admin.accounts', 'match' => 'admin.accounts*', 'label' => 'Quản lý tài khoản', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
            ['route' => 'admin.departments', 'match' => 'admin.departments*', 'label' => 'Quản lý phòng ban', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z'],
            ['route' => 'admin.positions', 'match' => 'admin.positions*', 'label' => 'Quản lý chức vụ', 'icon' => 'M20.25 14.15v4.25c0 .414-.336.75-.75.75h-4.5a.75.75 0 01-.75-.75v-4.25m0 0h4.125c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9m9 8.25H18M4.5 9.75h7.5m-7.5 3H9'],
            ['route' => 'admin.employees', 'match' => 'admin.employees*', 'label' => 'Quản lý nhân viên', 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
            [
                'route' => 'admin.attendances',
                'match' => 'admin.attendances*',
                'label' => 'Quản lý chấm công',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'children' => [
                    ['route' => 'admin.shifts.index', 'label' => 'Quản lý ca'],
                    ['route' => 'admin.leave-requests', 'label' => 'Quản lý nghỉ phép'],
                    ['route' => 'admin.overtime-requests.index', 'label' => 'Duyệt tăng ca'],
                    ['route' => 'admin.attendance-reports.index', 'label' => 'Báo cáo chấm công'],
                ],
            ],
            [
                'route' => 'admin.kpis.index',
                'match' => 'admin.kpis*',
                'label' => 'Quản lý KPI',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                'children' => [
                    ['route' => 'admin.kpi-assignments.index', 'label' => 'Giao KPI cho Manager'],
                ],
            ],
            [
                'route' => 'admin.payroll-periods.index',
                'match' => 'admin.payroll-periods*',
                'label' => 'Quản lý kỳ lương',
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            ['route' => 'admin.contracts.index', 'match' => 'admin.contracts.*', 'label' => 'Quản lý hợp đồng', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
            // ['route' => 'admin.leave-requests', 'match' => 'admin.leave-requests*', 'label' => 'Quản lý nghỉ phép', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z'],
            ['route' => 'admin.recruitment', 'match' => 'admin.recruitment*', 'label' => 'Tuyển dụng', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ];
    } else {
        $menuItems = [];
    }

    if ($user?->isAdmin()) {
        $menuItems = app(\App\Services\AdminPendingApprovalService::class)->applyBadgesToMenuItems($menuItems);
    }
@endphp

@foreach ($menuItems as $item)
    @continue(!Route::has($item['route']))

    @php
        $isActive = request()->routeIs($item['match'] ?? $item['route']);
    @endphp

    <div>
        <a href="{{ route($item['route']) }}"
           class="admin-menu-item group {{ $isActive ? 'admin-menu-item-active' : 'admin-menu-item-inactive' }}">
            <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-white/15' : 'bg-slate-100 text-slate-500 group-hover:bg-violet-100' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                @if (! empty($item['badge']) && $item['badge'] > 0)
                    <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                @endif
            </span>
            <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                <span class="truncate">{{ $item['label'] }}</span>
                @if (! empty($item['badge']) && $item['badge'] > 0)
                    <x-nav-badge :count="$item['badge']" />
                @endif
            </span>
        </a>

        @if(isset($item['children']))
            <div class="ml-5 mt-2 space-y-1 border-l border-slate-200/80 pl-5">
                @foreach($item['children'] as $child)
                    @if(Route::has($child['route']))
                        @php $childActive = request()->routeIs($child['route']); @endphp
                        <a href="{{ route($child['route']) }}"
                           class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $childActive ? 'bg-violet-50 text-violet-700' : 'text-slate-500 hover:bg-violet-50 hover:text-violet-700' }}">
                            <span class="truncate">{{ $child['label'] }}</span>
                            @if (! empty($child['badge']) && $child['badge'] > 0)
                                <x-nav-badge :count="$child['badge']" />
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endforeach
