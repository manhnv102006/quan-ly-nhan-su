@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = [
        'title' => 'Thông báo',
        'subtitle' => 'Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI.',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    @include('notifications.partials.content', [
        'indexRoute' => 'employee.notifications.index',
        'readAllRoute' => 'employee.notifications.read-all',
        'readRoute' => 'employee.notifications.read',
        'showRoute' => 'employee.notifications.show',
        'showAccent' => 'sky',
    ])
</x-dynamic-component>
