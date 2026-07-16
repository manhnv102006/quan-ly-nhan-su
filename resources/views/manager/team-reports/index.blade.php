<x-manager-layout title="Báo cáo tiến độ nhóm" subtitle="Từ Trưởng nhóm">
    <div class="manager-page space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Báo cáo tiến độ & kết quả nhóm</h2>
            <p class="text-sm text-slate-500">Báo cáo công việc Trưởng nhóm gửi lên</p>
        </div>

        <div class="manager-panel">
            <div class="manager-table-wrap overflow-x-auto">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>Báo cáo</th>
                            <th>Trưởng nhóm</th>
                            <th>Phòng ban</th>
                            <th class="text-center">Thành viên</th>
                            <th class="text-center">KPI HT</th>
                            <th class="text-center">Tiến độ TB</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <p class="font-semibold">{{ $report->title }}</p>
                                    <p class="text-xs text-slate-500">Kỳ {{ $report->periodLabel() }}</p>
                                </td>
                                <td>{{ $report->leaderEmployee?->full_name ?? '—' }}</td>
                                <td>{{ $report->leaderEmployee?->department?->department_name ?? '—' }}</td>
                                <td class="text-center font-semibold">{{ $report->member_count }}</td>
                                <td class="text-center">{{ $report->kpi_completed }}/{{ $report->kpi_total }}</td>
                                <td class="text-center font-semibold text-teal-700">{{ $report->avg_kpi_progress }}%</td>
                                <td><span class="manager-badge {{ $report->status_tailwind }}">{{ $report->status_label }}</span></td>
                                <td>{{ $report->submitted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('manager.team-reports.show', $report) }}" class="manager-btn-secondary px-3 py-2 text-xs">Xem</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">Chưa có báo cáo nhóm nào được gửi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $reports->links() }}</div>
            @endif
        </div>
    </div>
</x-manager-layout>
