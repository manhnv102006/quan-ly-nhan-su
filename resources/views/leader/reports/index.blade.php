<x-leader-layout title="Báo cáo nhóm" subtitle="Tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Báo cáo tiến độ & kết quả nhóm</h2>
                <p class="text-sm text-slate-500">Tổng hợp KPI, chấm công và gửi báo cáo lên Manager</p>
            </div>
            <a href="{{ route('leader.reports.export', ['month' => $month, 'year' => $year]) }}" class="leader-btn-secondary">Xuất CSV</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[120px]">
                <label class="leader-label">Tháng</label>
                <select name="month" class="leader-field">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month === $m)>Tháng {{ $m }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="leader-label">Năm</label>
                <select name="year" class="leader-field">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="leader-btn-primary">Xem báo cáo</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $report['totals']['members']])
            @include('leader.partials.stat-card', ['label' => 'KPI hoàn thành', 'value' => $report['totals']['kpi_completed'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'KPI tổng', 'value' => $report['totals']['kpi_total'] ?? 0])
            @include('leader.partials.stat-card', ['label' => 'TB tiến độ KPI', 'value' => ($report['totals']['kpi_avg_progress'] ?? 0).'%', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Ngày công', 'value' => $report['totals']['work_days'], 'tone' => 'text-sky-600'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết theo thành viên</h3>
            </div>
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
                                    <a href="{{ route('leader.employees.show', $row['employee']) }}" class="font-semibold text-violet-800 hover:underline">{{ $row['employee']->full_name }}</a>
                                </td>
                                <td class="px-4 py-3">{{ $row['employee']->department?->department_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-emerald-700">{{ $row['kpi_completed'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['kpi_total'] }}</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $row['kpi_avg_progress'] }}%</td>
                                <td class="px-4 py-3 text-center">{{ $row['work_days'] }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $row['late_days'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Chưa có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="leader-card p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Gửi báo cáo lên Manager</h3>
                    <p class="mt-1 text-xs text-slate-500">Báo cáo tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }} · số liệu KPI & chấm công được đính kèm tự động</p>
                    @if($report['existing']?->isSubmitted())
                        <span class="mt-2 inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $report['existing']->status_tailwind }}">{{ $report['existing']->status_label }}</span>
                    @endif
                </div>
                @if($report['existing']?->isSubmitted())
                    <a href="{{ route('leader.reports.show', $report['existing']) }}" class="leader-btn-secondary">Xem báo cáo đã gửi</a>
                @endif
            </div>

            @if(! $report['manager_user_id'])
                <p class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">Chưa xác định được Manager phòng ban. Vui lòng liên hệ quản trị trước khi gửi báo cáo.</p>
            @elseif(! $report['existing']?->isSubmitted())
                <form method="POST" action="{{ route('leader.reports.submit') }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="period_month" value="{{ $month }}">
                    <input type="hidden" name="period_year" value="{{ $year }}">
                    <div>
                        <label class="leader-label">Tiến độ công việc <span class="text-rose-500">*</span></label>
                        <textarea name="work_progress" rows="4" class="leader-field" required placeholder="Mô tả tiến độ công việc nhóm trong tháng...">{{ old('work_progress', $report['existing']?->work_progress) }}</textarea>
                        @error('work_progress')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="leader-label">Kết quả nhóm <span class="text-rose-500">*</span></label>
                        <textarea name="team_results" rows="4" class="leader-field" required placeholder="Kết quả đạt được, thành tích nổi bật...">{{ old('team_results', $report['existing']?->team_results) }}</textarea>
                        @error('team_results')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="leader-label">Khó khăn / đề xuất</label>
                        <textarea name="notes" rows="3" class="leader-field" placeholder="Khó khăn gặp phải, đề xuất hỗ trợ...">{{ old('notes', $report['existing']?->notes) }}</textarea>
                    </div>
                    <button type="submit" class="leader-btn-primary">Gửi báo cáo lên Manager</button>
                </form>
            @else
                <div class="mt-4 space-y-3 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm">
                    <div><p class="text-xs font-bold uppercase text-slate-400">Tiến độ công việc</p><p class="mt-1 whitespace-pre-wrap">{{ $report['existing']->work_progress }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-slate-400">Kết quả nhóm</p><p class="mt-1 whitespace-pre-wrap">{{ $report['existing']->team_results }}</p></div>
                    @if($report['existing']->notes)
                        <div><p class="text-xs font-bold uppercase text-slate-400">Ghi chú</p><p class="mt-1 whitespace-pre-wrap">{{ $report['existing']->notes }}</p></div>
                    @endif
                    @if($report['existing']->manager_review)
                        <div><p class="text-xs font-bold uppercase text-slate-400">Phản hồi Manager</p><p class="mt-1 whitespace-pre-wrap">{{ $report['existing']->manager_review }}</p></div>
                    @endif
                </div>
            @endif
        </div>

        @if($history->isNotEmpty())
            <div class="leader-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Lịch sử báo cáo đã gửi</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($history as $item)
                        <a href="{{ route('leader.reports.show', $item) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 transition hover:bg-violet-50/30">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $item->title }}</p>
                                <p class="text-xs text-slate-500">Gửi: {{ $item->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $item->status_tailwind }}">{{ $item->status_label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-leader-layout>
