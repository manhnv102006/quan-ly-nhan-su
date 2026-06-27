<x-staff-layout
    title="Thông báo"
    subtitle="Cập nhật tự động từ hệ thống về nghỉ phép, lương và KPI."
    role="employee"
    :navigation="$navigation"
>
    @include('notifications.partials.content', [
        'indexRoute' => 'employee.notifications.index',
        'readAllRoute' => 'employee.notifications.read-all',
        'readRoute' => 'employee.notifications.read',
    ])
</x-staff-layout>
