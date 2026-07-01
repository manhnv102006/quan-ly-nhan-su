<x-admin-layout title="Báo cáo chấm công">

    <div class="space-y-6">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Báo cáo chấm công</h1>
            <p class="mt-1 text-sm text-slate-500">
                Tháng {{ $month }}/{{ $year }} — chọn phòng ban để xem báo cáo chi tiết
            </p>
        </div>

        @include('admin.attendance-reports.partials.report-filter', [
            'filterAction' => route('admin.attendance-reports.index'),
        ])

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.attendance-reports.department',
            'routeParams' => ['month' => $month, 'year' => $year],
            'statLabels' => ['NV chấm công', 'Đi làm', 'Đi muộn'],
            'statKeys' => ['employee_count', 'work_days', 'late'],
            'statTones' => ['slate', 'emerald', 'amber'],
        ])
    </div>

</x-admin-layout>
