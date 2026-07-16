<x-leader-layout title="Báo cáo nhóm" subtitle="Tháng {{ str_pad($report['month'], 2, '0', STR_PAD_LEFT) }}/{{ $report['year'] }}">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Báo cáo nhóm</h2>
                <p class="text-sm text-slate-500">KPI và chấm công tháng {{ $report['month'] }}/{{ $report['year'] }}</p>
            </div>
            <a href="{{ route('leader.reports.export') }}" class="leader-btn-primary">Xuất CSV</a>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $report['totals']['members']])
            @include('leader.partials.stat-card', ['label' => 'KPI hoàn thành', 'value' => $report['totals']['kpi_completed'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Tổng ngày công', 'value' => $report['totals']['work_days'], 'tone' => 'text-violet-700'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3 text-center">KPI HT</th>
                            <th class="px-4 py-3 text-center">KPI tổng</th>
                            <th class="px-4 py-3 text-center">TB tiến độ</th>
                            <th class="px-4 py-3 text-center">Ngày công</th>
                            <th class="px-4 py-3 text-center">Đi muộn</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($report['rows'] as $row)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <a href="{{ route('leader.employees.show', $row['employee']) }}" class="font-semibold text-violet-800 hover:underline">
                                        {{ $row['employee']->full_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">{{ $row['employee']->department?->department_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-emerald-700">{{ $row['kpi_completed'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['kpi_total'] }}</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $row['kpi_avg_progress'] }}%</td>
                                <td class="px-4 py-3 text-center">{{ $row['work_days'] }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $row['late_days'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center text-slate-500">Chưa có dữ liệu báo cáo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-leader-layout>
