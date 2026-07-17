<x-manager-layout title="Báo cáo KPI nhóm" subtitle="Báo cáo Trưởng nhóm gửi lên">
    <div class="manager-page space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Báo cáo KPI nhóm từ Trưởng nhóm</h2>
            <p class="text-sm text-slate-500">Xem và phê duyệt báo cáo kết quả KPI nhóm</p>
        </div>

        <div class="manager-panel">
            <div class="manager-table-wrap overflow-x-auto">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>KPI</th>
                            <th>Trưởng nhóm</th>
                            <th class="text-center">Thành viên</th>
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
                                    <p class="font-semibold">{{ $report->assignment?->kpi_title ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $report->assignment?->kpi_code }}</p>
                                </td>
                                <td>{{ $report->leaderEmployee?->full_name ?? '—' }}</td>
                                <td class="text-center font-semibold">{{ $report->total_members }}</td>
                                <td class="text-center font-semibold text-teal-700">{{ $report->avg_progress }}%</td>
                                <td><span class="manager-badge {{ $report->status_tailwind }}">{{ $report->status_label }}</span></td>
                                <td>{{ $report->submitted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('manager.kpi-reports.show', $report) }}" class="manager-btn-secondary px-3 py-2 text-xs">Xem</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">Chưa có báo cáo KPI nhóm nào được gửi.</td>
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
