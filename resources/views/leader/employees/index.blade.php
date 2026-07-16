<x-leader-layout title="Nhân viên" subtitle="Thành viên nhóm">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Nhân viên trong nhóm</h2>
                <p class="text-sm text-slate-500">Báo cáo trực tiếp cho {{ $leader->full_name }}</p>
            </div>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[200px] flex-1">
                <label class="leader-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tên, mã NV..." class="leader-field">
            </div>
            <div class="min-w-[160px]">
                <label class="leader-label">Trạng thái</label>
                <select name="status" class="leader-field">
                    <option value="">Tất cả</option>
                    <option value="active" @selected($status === 'active')>Đang làm</option>
                    <option value="inactive" @selected($status === 'inactive')>Tạm nghỉ</option>
                    <option value="resigned" @selected($status === 'resigned')>Đã nghỉ</option>
                </select>
            </div>
            <button type="submit" class="leader-btn-primary">Lọc</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Tổng', 'value' => $stats['total']])
            @include('leader.partials.stat-card', ['label' => 'Đang làm', 'value' => $stats['active'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Tạm nghỉ', 'value' => $stats['inactive'], 'tone' => 'text-amber-600'])
            @include('leader.partials.stat-card', ['label' => 'Đã nghỉ', 'value' => $stats['resigned'], 'tone' => 'text-slate-500'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3">Chức vụ</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $employee->department?->department_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="leader-badge {{ $employee->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('leader.employees.show', $employee) }}" class="leader-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-slate-500">Chưa có nhân viên trong nhóm.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
