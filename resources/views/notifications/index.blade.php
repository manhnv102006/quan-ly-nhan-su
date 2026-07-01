@php
    $user = auth()->user();
@endphp

@if ($user->isAdmin())
    <x-admin-layout title="Thông báo">
        @include('notifications.partials.content', [
            'showRoute' => 'notifications.show',
            'showAccent' => 'violet',
        ])
    </x-admin-layout>
@else
    @php
        $staffNavigation = [
            [
                'label' => 'Dashboard',
                'href' => route($user->dashboardRouteName()),
                'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
            ],
            [
                'label' => 'Thông báo',
                'href' => route('notifications.index'),
                'route' => 'notifications*',
                'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
                'active' => true,
            ],
        ];
    @endphp
    <x-staff-layout
        title="Thông báo"
        subtitle="Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI."
        role="employee"
        :navigation="$staffNavigation"
    >
        @include('notifications.partials.content')
    </x-staff-layout>
@endif
