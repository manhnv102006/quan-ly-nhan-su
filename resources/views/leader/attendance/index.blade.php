<x-leader-layout title="Bảng công nhóm" subtitle="Chấm công thành viên · Chỉ xem">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Bảng công nhóm</h2>
                <p class="text-sm text-slate-500">Theo dõi chấm công thành viên báo cáo cho {{ $leader->full_name }}</p>
            </div>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="leader-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="leader-field">
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="leader-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tên, mã NV..." class="leader-field">
            </div>
            <button type="submit" class="leader-btn-primary">Lọc</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('leader.partials.stat-card', ['label' => 'Ngày công tháng', 'value' => $monthlySummary['payable_days'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Tổng giờ làm', 'value' => $monthlySummary['total_hours'].'h', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Đi muộn', 'value' => $monthlySummary['late_days'], 'tone' => 'text-amber-600'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3 text-center">Ngày công</th>
                            <th class="px-4 py-3 text-center">Đúng giờ</th>
                            <th class="px-4 py-3 text-center">Muộn</th>
                            <th class="px-4 py-3 text-center">Vắng</th>
                            <th class="px-4 py-3 text-center">Giờ làm</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            @php $stats = $employee->attendance_stats; @endphp
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-emerald-700">{{ $employee->payable_days ?? 0 }}</td>
                                <td class="px-4 py-3 text-center">{{ $stats->present ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $stats->late ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-rose-600">{{ $stats->absent ?? 0 }}</td>
                                <td class="px-4 py-3 text-center">{{ isset($stats->total_hours) ? round((float) $stats->total_hours, 1) : 0 }}h</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('leader.attendance.index', ['month' => $month, 'employee_id' => $employee->id]) }}" class="leader-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-slate-500">Chưa có thành viên trong nhóm.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $employees->links() }}</div>
            @endif
        </div>

        @if($detailEmployee)
            <div class="leader-card overflow-hidden">
                <div class="border-b border-violet-100/80 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Chi tiết chấm công — {{ $detailEmployee->full_name }}</h3>
                    <p class="text-xs text-slate-500">Tháng {{ str_pad($monthNum, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</p>
                </div>
                <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-100 px-5 py-4">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="employee_id" value="{{ $detailEmployee->id }}">
                    <div>
                        <label class="leader-label">Trạng thái</label>
                        <select name="status" class="leader-field">
                            <option value="">Tất cả</option>
                            @foreach(['present' => 'Đúng giờ', 'late' => 'Muộn', 'absent' => 'Vắng', 'leave' => 'Nghỉ phép'] as $key => $label)
                                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="leader-btn-primary">Lọc</button>
                </form>
                <div class="divide-y divide-slate-100">
                    @forelse($detailAttendances as $attendance)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $attendance->attendance_date?->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-500">{{ $attendance->shift?->shift_name ?? 'Ca làm' }} · Vào {{ $attendance->check_in?->format('H:i') ?? '--:--' }} · Ra {{ $attendance->check_out?->format('H:i') ?? '--:--' }}</p>
                            </div>
                            <span class="leader-badge {{ $attendance->status === 'late' ? 'bg-amber-100 text-amber-700' : ($attendance->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ $attendance->status }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-10 text-center text-sm text-slate-400">Không có dữ liệu chấm công trong tháng.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-leader-layout>
