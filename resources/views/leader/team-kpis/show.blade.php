<x-leader-layout :title="'KPI nhóm: ' . $assignment->kpi_title" subtitle="Phân bổ · theo dõi · báo cáo">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('leader.team-kpis.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">← KPI nhóm từ Manager</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $assignment->kpi_title }}</h2>
                <p class="text-sm text-slate-500">{{ $assignment->kpi_code }} · Chỉ tiêu nhóm: {{ $assignment->formatted_target }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leader.team-kpis.allocate', $assignment) }}" class="leader-btn-primary">+ Phân bổ KPI cá nhân</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Thành viên có KPI', 'value' => $summary['total_members']])
            @include('leader.partials.stat-card', ['label' => 'Hoàn thành', 'value' => $summary['completed_count'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Tiến độ TB', 'value' => $summary['avg_progress'].'%', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Điểm TB (Leader)', 'value' => $summary['avg_leader_score'] ?? '—'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Chỉ tiêu chung từ Manager</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Mục tiêu nhóm</dt><dd class="font-semibold">{{ $assignment->formatted_target }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Thời gian</dt><dd>{{ $assignment->start_date->format('d/m/Y') }} – {{ $assignment->end_date->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-slate-400">Mô tả</dt><dd>{{ $assignment->kpi?->description ?? '—' }}</dd></div>
                    @if($assignment->note)
                        <div><dt class="text-xs font-bold uppercase text-slate-400">Ghi chú Manager</dt><dd>{{ $assignment->note }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Nhiệm vụ KPI</h3>
                <ul class="mt-4 space-y-2">
                    @forelse($assignment->kpi?->tasks ?? [] as $task)
                        <li class="rounded-xl border border-violet-100 bg-violet-50/30 px-4 py-3 text-sm">
                            <p class="font-semibold text-slate-800">{{ $task->title }}</p>
                            @if($task->description)<p class="mt-1 text-xs text-slate-500">{{ $task->description }}</p>@endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Chưa có nhiệm vụ chi tiết.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="leader-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Phân bổ KPI cá nhân ({{ $assignment->employeeKpis->count() }})</h3>
                <p class="text-xs text-slate-500">Theo dõi tiến độ và chấm điểm từng thành viên</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Thành viên</th>
                            <th class="px-4 py-3">Mục tiêu cá nhân</th>
                            <th class="px-4 py-3 text-center">Tiến độ</th>
                            <th class="px-4 py-3 text-center">Điểm Leader</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignment->employeeKpis as $goal)
                            @php $progress = max(0, min(100, (int) ($goal->progress ?? 0))); @endphp
                            <tr class="hover:bg-violet-50/20">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $goal->employee?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $goal->employee?->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $goal->target }}</p>
                                    @if($goal->comment)<p class="text-xs text-slate-500">{{ Str::limit($goal->comment, 60) }}</p>@endif
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-violet-700">{{ $progress }}%</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $goal->leader_score ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $goal->status_label }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('leader.kpis.show', $goal) }}" class="leader-btn-secondary !py-1.5 !text-xs">Xem</a>
                                        <a href="{{ route('leader.team-kpis.edit_allocation', [$assignment, $goal]) }}" class="leader-btn-secondary !py-1.5 !text-xs">Sửa</a>
                                        <a href="{{ route('leader.kpis.score.edit', $goal) }}" class="leader-btn-primary !py-1.5 !text-xs">Chấm điểm</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">Chưa phân bổ KPI cá nhân. Nhấn "Phân bổ KPI cá nhân" để bắt đầu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="leader-card p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Báo cáo tổng hợp KPI nhóm</h3>
                    <p class="mt-1 text-xs text-slate-500">Tự động cộng dồn từ KPI cá nhân · gửi lên Manager khi sẵn sàng</p>
                    @if($report->isSubmitted())
                        <span class="mt-2 inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $report->status_tailwind }}">{{ $report->status_label }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4 text-sm">
                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-500">Thành viên</p><p class="font-bold">{{ $report->total_members }}</p></div>
                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-500">Hoàn thành</p><p class="font-bold text-emerald-700">{{ $report->completed_count }}</p></div>
                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-500">Tiến độ TB</p><p class="font-bold text-violet-700">{{ $report->avg_progress }}%</p></div>
                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-500">Điểm TB</p><p class="font-bold">{{ $report->avg_leader_score ?? '—' }}</p></div>
            </div>

            @if(! $report->isSubmitted())
                <form method="POST" action="{{ route('leader.team-kpis.submit_report', $assignment) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="leader-label">Tóm tắt báo cáo gửi Manager</label>
                        <textarea name="summary" rows="4" class="leader-field" placeholder="Tóm tắt kết quả KPI nhóm, điểm nổi bật, khó khăn...">{{ old('summary', $report->summary) }}</textarea>
                        @error('summary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="leader-btn-primary">Gửi báo cáo lên Manager</button>
                </form>
            @else
                <div class="mt-5 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm">
                    <p class="text-xs font-bold uppercase text-slate-400">Nội dung đã gửi</p>
                    <p class="mt-2 whitespace-pre-wrap text-slate-700">{{ $report->summary ?? '—' }}</p>
                    <p class="mt-2 text-xs text-slate-500">Gửi lúc: {{ $report->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
                    @if($report->manager_review)
                        <p class="mt-3 text-xs font-bold uppercase text-slate-400">Phản hồi Manager</p>
                        <p class="mt-1 whitespace-pre-wrap text-slate-700">{{ $report->manager_review }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-leader-layout>
