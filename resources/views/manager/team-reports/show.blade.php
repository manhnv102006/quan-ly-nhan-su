<x-manager-layout :title="$report->title" subtitle="Báo cáo từ Trưởng nhóm">
    <div class="manager-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('manager.team-reports.index') }}" class="text-sm text-teal-600 hover:underline">← Danh sách báo cáo</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $report->title }}</h2>
                <p class="text-sm text-slate-500">
                    Trưởng nhóm: {{ $report->leaderEmployee?->full_name ?? '—' }}
                    · {{ $report->leaderEmployee?->department?->department_name ?? '—' }}
                </p>
            </div>
            <span class="manager-badge {{ $report->status_tailwind }}">{{ $report->status_label }}</span>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Thành viên</p><p class="text-2xl font-bold">{{ $report->member_count }}</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">KPI hoàn thành</p><p class="text-2xl font-bold text-emerald-600">{{ $report->kpi_completed }}/{{ $report->kpi_total }}</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Tiến độ TB</p><p class="text-2xl font-bold text-teal-700">{{ $report->avg_kpi_progress }}%</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Ngày công</p><p class="text-2xl font-bold">{{ $report->total_work_days }}</p></div>
            <div class="manager-panel p-4 text-center"><p class="text-xs text-slate-500">Đi muộn</p><p class="text-2xl font-bold text-amber-600">{{ $report->total_late_days }}</p></div>
        </div>

        <div class="manager-panel p-6 space-y-5">
            <div>
                <h3 class="font-semibold text-slate-800">Tiến độ công việc</h3>
                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $report->work_progress }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">Kết quả nhóm</h3>
                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $report->team_results }}</p>
            </div>
            @if($report->notes)
                <div>
                    <h3 class="font-semibold text-slate-800">Khó khăn / đề xuất</h3>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $report->notes }}</p>
                </div>
            @endif
            <p class="text-xs text-slate-500">Gửi lúc: {{ $report->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>

        @if($report->status === \App\Models\LeaderTeamReport::STATUS_SUBMITTED)
            <div class="manager-panel p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Phê duyệt báo cáo</h3>
                <form method="POST" action="{{ route('manager.team-reports.review', $report) }}" class="space-y-4">
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
                <h3 class="font-semibold text-slate-800">Phản hồi của bạn</h3>
                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $report->manager_review }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $report->reviewedBy?->name ?? '—' }} · {{ $report->reviewed_at?->format('d/m/Y H:i') }}</p>
            </div>
        @endif
    </div>
</x-manager-layout>
