<x-manager-layout :title="'Báo cáo KPI: ' . ($report->assignment?->kpi_title ?? '')" subtitle="Chi tiết báo cáo từ Trưởng nhóm">
    <div class="manager-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('manager.kpi-reports.index') }}" class="text-sm text-teal-600 hover:underline">← Danh sách báo cáo</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $report->assignment?->kpi_title ?? 'Báo cáo KPI nhóm' }}</h2>
                <p class="text-sm text-slate-500">Trưởng nhóm: {{ $report->leaderEmployee?->full_name ?? '—' }}</p>
            </div>
            <span class="manager-badge {{ $report->status_tailwind }}">{{ $report->status_label }}</span>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Thành viên</p><p class="text-2xl font-bold">{{ $report->total_members }}</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Hoàn thành</p><p class="text-2xl font-bold text-emerald-600">{{ $report->completed_count }}</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Tiến độ TB</p><p class="text-2xl font-bold text-teal-700">{{ $report->avg_progress }}%</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Điểm TB Leader</p><p class="text-2xl font-bold">{{ $report->avg_leader_score ?? '—' }}</p></div>
        </div>

        <div class="manager-panel p-6">
            <h3 class="font-semibold text-slate-800">Tóm tắt từ Trưởng nhóm</h3>
            <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700">{{ $report->summary ?? '—' }}</p>
            <p class="mt-2 text-xs text-slate-500">Gửi lúc: {{ $report->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>

        <div class="manager-panel">
            <div class="manager-panel-header"><h3 class="manager-section-title text-lg">Chi tiết KPI cá nhân</h3></div>
            <div class="manager-table-wrap overflow-x-auto">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Mục tiêu</th>
                            <th class="text-center">Tiến độ</th>
                            <th class="text-center">Điểm Leader</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Chấm KPI (Manager)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->assignment?->employeeKpis ?? [] as $goal)
                            <tr>
                                <td>{{ $goal->employee?->full_name ?? '—' }}</td>
                                <td>{{ $goal->target }}</td>
                                <td class="text-center font-semibold">{{ $goal->progress }}%</td>
                                <td class="text-center font-semibold">{{ $goal->leader_score ?? '—' }}</td>
                                <td>{{ $goal->status_label }}</td>
                                <td class="text-center">
                                    <a href="{{ route('manager.kpis.employee_kpis.score.edit', $goal) }}" class="manager-btn-primary px-3 py-2 text-xs">Chấm điểm</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($report->status === \App\Models\KpiTeamReport::STATUS_SUBMITTED)
            <div class="manager-panel p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Phê duyệt báo cáo</h3>
                <form method="POST" action="{{ route('manager.kpi-reports.review', $report) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nhận xét Manager</label>
                        <textarea name="manager_review" rows="3" class="w-full rounded-xl border border-slate-300">{{ old('manager_review') }}</textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" name="action" value="approve" class="manager-btn-primary">Phê duyệt</button>
                        <button type="submit" name="action" value="reject" class="manager-btn-secondary !border-rose-200 !text-rose-700">Từ chối</button>
                    </div>
                </form>
            </div>
        @elseif($report->manager_review)
            <div class="manager-panel p-6">
                <h3 class="font-semibold text-slate-800">Phản hồi Manager</h3>
                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $report->manager_review }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $report->reviewedBy?->name ?? '—' }} · {{ $report->reviewed_at?->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>
</x-manager-layout>
