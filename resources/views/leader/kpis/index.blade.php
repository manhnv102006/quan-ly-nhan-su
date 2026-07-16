<x-leader-layout title="Tiến độ KPI cá nhân" subtitle="Theo dõi KPI đã phân bổ cho thành viên">
    <div class="leader-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Tiến độ KPI cá nhân</h2>
                <p class="text-sm text-slate-500">Xem tiến độ và chấm điểm KPI từng thành viên trong nhóm</p>
            </div>
            <a href="{{ route('leader.team-kpis.index') }}" class="leader-btn-primary">KPI nhóm từ Manager →</a>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[180px]">
                <label class="leader-label">Trạng thái</label>
                <select name="status" class="leader-field">
                    <option value="">Tất cả</option>
                    <option value="pending" @selected($status === 'pending')>Chờ</option>
                    <option value="in_progress" @selected($status === 'in_progress')>Đang làm</option>
                    <option value="completed" @selected($status === 'completed')>Hoàn thành</option>
                    <option value="not_completed" @selected($status === 'not_completed')>Không hoàn thành</option>
                </select>
            </div>
            <button type="submit" class="leader-btn-primary">Lọc</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Tổng', 'value' => $stats['total']])
            @include('leader.partials.stat-card', ['label' => 'Chờ', 'value' => $stats['pending'], 'tone' => 'text-amber-600'])
            @include('leader.partials.stat-card', ['label' => 'Đang làm', 'value' => $stats['in_progress'], 'tone' => 'text-sky-600'])
            @include('leader.partials.stat-card', ['label' => 'Hoàn thành', 'value' => $stats['completed'], 'tone' => 'text-emerald-600'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">KPI</th>
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Mục tiêu</th>
                            <th class="px-4 py-3 text-center">Tiến độ</th>
                            <th class="px-4 py-3 text-center">Điểm Leader</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Hạn</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employeeKpis as $ek)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3 font-semibold text-violet-800">{{ $ek->kpi?->title ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $ek->employee?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $ek->target ?? '—' }}</td>
                                <td class="px-4 py-3 text-center"><span class="font-bold text-violet-700">{{ $ek->progress }}%</span></td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $ek->leader_score ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $ek->status_label }}</td>
                                <td class="px-4 py-3">{{ $ek->deadline?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('leader.kpis.show', $ek) }}" class="leader-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                        <a href="{{ route('leader.kpis.score.edit', $ek) }}" class="leader-btn-primary !py-1.5 !text-xs">Chấm</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center text-slate-500">
                                    Chưa có KPI cá nhân. Vào <a href="{{ route('leader.team-kpis.index') }}" class="font-semibold text-violet-700 hover:underline">KPI nhóm từ Manager</a> để phân bổ.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employeeKpis->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $employeeKpis->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
