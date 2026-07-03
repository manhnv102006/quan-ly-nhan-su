<x-admin-layout title="Quản lý chấm công">
     <div class="space-y-6">

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.attendances.department',
            'statLabels' => ['NV chấm công', 'Đi làm', 'Đi muộn'],
            'statKeys' => ['employee_count', 'work_days', 'late'],
            'statTones' => ['slate', 'emerald', 'amber'],
        ])
    </div>

</x-admin-layout>
