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
@elseif ($user->isManager())
    <x-manager-layout title="Thông báo" subtitle="Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI.">
        @include('notifications.partials.content')
    </x-manager-layout>
@else
    <x-employee-layout title="Thông báo" subtitle="Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI.">
        @include('notifications.partials.content')
    </x-employee-layout>
@endif
