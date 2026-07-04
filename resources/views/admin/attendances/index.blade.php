<x-admin-layout title="Quản lý chấm công">

    <div class="space-y-6">

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Quản lý chấm công</h1>
            <p class="mt-1 text-sm text-slate-500">
                Chọn phòng ban để xem và quản lý dữ liệu chấm công
            </p>
        </div>

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.attendances.department',
            'statLabels' => ['NV chấm công', 'Đi làm', 'Đi muộn'],
            'statKeys' => ['employee_count', 'work_days', 'late'],
            'statTones' => ['slate', 'emerald', 'amber'],
        ])
    </div>

</x-admin-layout>
