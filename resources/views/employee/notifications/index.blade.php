<x-employee-layout
    title="Thông báo"
    subtitle="Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI."
>
    @include('notifications.partials.content', [
        'indexRoute' => 'employee.notifications.index',
        'readAllRoute' => 'employee.notifications.read-all',
        'readRoute' => 'employee.notifications.read',
        'showRoute' => 'employee.notifications.show',
        'showAccent' => 'sky',
    ])
</x-employee-layout>
