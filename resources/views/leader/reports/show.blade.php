<x-leader-layout :title="$submission->title" subtitle="Báo cáo đã gửi Manager">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('leader.reports.index', ['month' => $submission->period_month, 'year' => $submission->period_year]) }}" class="text-xs font-semibold text-violet-700 hover:underline">← Báo cáo nhóm</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $submission->title }}</h2>
                <p class="text-sm text-slate-500">Gửi lúc: {{ $submission->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            <span class="inline-flex rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $submission->status_tailwind }}">{{ $submission->status_label }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $submission->member_count])
            @include('leader.partials.stat-card', ['label' => 'KPI HT', 'value' => $submission->kpi_completed, 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'KPI tổng', 'value' => $submission->kpi_total])
            @include('leader.partials.stat-card', ['label' => 'TB tiến độ', 'value' => $submission->avg_kpi_progress.'%'])
            @include('leader.partials.stat-card', ['label' => 'Ngày công', 'value' => $submission->total_work_days])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card p-5 space-y-4">
                <div><h3 class="text-xs font-bold uppercase text-slate-400">Tiến độ công việc</h3><p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $submission->work_progress }}</p></div>
                <div><h3 class="text-xs font-bold uppercase text-slate-400">Kết quả nhóm</h3><p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $submission->team_results }}</p></div>
                @if($submission->notes)
                    <div><h3 class="text-xs font-bold uppercase text-slate-400">Khó khăn / đề xuất</h3><p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $submission->notes }}</p></div>
                @endif
                @if($submission->manager_review)
                    <div class="rounded-xl border border-teal-100 bg-teal-50/50 p-4">
                        <h3 class="text-xs font-bold uppercase text-teal-700">Phản hồi Manager</h3>
                        <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $submission->manager_review }}</p>
                    </div>
                @endif
            </div>

            <div class="leader-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4"><h3 class="text-sm font-bold text-slate-800">Chi tiết thành viên</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-violet-50/50 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-4 py-2">NV</th>
                                <th class="px-4 py-2 text-center">KPI</th>
                                <th class="px-4 py-2 text-center">Tiến độ</th>
                                <th class="px-4 py-2 text-center">Công</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($report['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-2 font-medium">{{ $row['employee']->full_name }}</td>
                                    <td class="px-4 py-2 text-center">{{ $row['kpi_completed'] }}/{{ $row['kpi_total'] }}</td>
                                    <td class="px-4 py-2 text-center">{{ $row['kpi_avg_progress'] }}%</td>
                                    <td class="px-4 py-2 text-center">{{ $row['work_days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-leader-layout>
