<x-leader-layout title="KPI nhóm từ Manager" subtitle="Chỉ tiêu chung được giao cho nhóm">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">KPI nhóm từ Manager</h2>
                <p class="text-sm text-slate-500">Nhận chỉ tiêu chung, phân bổ cho thành viên và gửi báo cáo lên Manager.</p>
            </div>
            <a href="{{ route('leader.kpis.index') }}" class="leader-btn-secondary">Tiến độ KPI cá nhân →</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">KPI</th>
                            <th class="px-4 py-3">Chỉ tiêu nhóm</th>
                            <th class="px-4 py-3">Thời hạn</th>
                            <th class="px-4 py-3 text-center">Đã phân bổ</th>
                            <th class="px-4 py-3">Báo cáo</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $assignment)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-violet-800">{{ $assignment->kpi_title }}</p>
                                    <p class="text-xs text-slate-500">{{ $assignment->kpi_code }}</p>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $assignment->formatted_target }}</td>
                                <td class="px-4 py-3">{{ $assignment->start_date->format('d/m/Y') }} – {{ $assignment->end_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-center font-bold text-violet-700">{{ $assignment->employee_kpis_count }}</td>
                                <td class="px-4 py-3">
                                    @if($assignment->teamReport)
                                        <span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $assignment->teamReport->status_tailwind }}">
                                            {{ $assignment->teamReport->status_label }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Chưa gửi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('leader.team-kpis.show', $assignment) }}" class="leader-btn-primary !py-1.5 !text-xs">Quản lý</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-slate-500">
                                    Chưa có KPI nhóm nào được Manager giao. Manager cần giao KPI nhóm cho bạn trước.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assignments->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $assignments->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
