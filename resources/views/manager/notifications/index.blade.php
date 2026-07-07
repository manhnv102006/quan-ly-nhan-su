<x-manager-layout
    title="Thông báo"
    subtitle="{{ $managedDepartment ? 'Thông báo phòng ban '.$managedDepartment->department_name : 'Chưa gắn phòng ban quản lý' }}"
>
    @include('notifications.partials.content', [
        'indexRoute' => 'manager.notifications.index',
        'readAllRoute' => 'manager.notifications.read-all',
        'readRoute' => 'manager.notifications.read',
        'createRoute' => $managedDepartment ? 'manager.notifications.create' : null,
        'showRoute' => 'manager.notifications.show',
        'showAccent' => 'emerald',
    ])
</x-manager-layout>
