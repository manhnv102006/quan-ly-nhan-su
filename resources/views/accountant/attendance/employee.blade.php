@php
    $monthLabel = str_pad($filters['month'], 2, '0', STR_PAD_LEFT).'/'.$filters['year'];
@endphp

<x-accountant-layout title="Bảng công - {{ $employee->full_name }}" subtitle="Tháng {{ $monthLabel }}">
    @include('accountant.attendance.partials.sub-nav', ['active' => 'departments'])

    <div class="accountant-page">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            Chế độ chỉ xem — bảng công dùng đối chiếu khi tính lương kỳ {{ $monthLabel }}.
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.attendance.index') }}" class="text-emerald-700 hover:underline">Phòng ban</a>
                    @if($department)
                        <span>/</span>
                        <a href="{{ route('accountant.attendance.index', ['department_id' => $department->id, 'month' => $month]) }}" class="text-emerald-700 hover:underline">{{ $department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-700">{{ $employee->full_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                    · {{ $department?->department_name ?? '—' }}
                </p>
            </div>
            @if($department)
                <a href="{{ route('accountant.attendance.index', ['department_id' => $department->id, 'month' => $month]) }}" class="accountant-btn-secondary">← Phòng ban</a>
            @endif
        </div>

        <form method="GET" action="{{ route('accountant.attendance.index') }}" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="accountant-field">
            </div>
            <div class="min-w-[160px]">
                <label class="accountant-label">Trạng thái</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                    <option value="late" @selected($filters['status'] === 'late')>Đi muộn</option>
                    <option value="absent" @selected($filters['status'] === 'absent')>Vắng mặt</option>
                    <option value="leave" @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Xem</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-8">
            @foreach([
                ['label' => 'Tổng ngày', 'value' => $summary['total'], 'tone' => 'text-slate-800'],
                ['label' => 'Ngày công TL', 'value' => $summary['payable_days'], 'tone' => 'text-emerald-600'],
                ['label' => 'Đi làm', 'value' => $summary['present'], 'tone' => 'text-emerald-600'],
                ['label' => 'Muộn', 'value' => $summary['late'], 'tone' => 'text-amber-600'],
                ['label' => 'Vắng', 'value' => $summary['absent'], 'tone' => 'text-rose-600'],
                ['label' => 'Nghỉ phép', 'value' => $summary['leave'], 'tone' => 'text-sky-600'],
                ['label' => 'Giờ làm', 'value' => $summary['total_hours'].'h', 'tone' => 'text-indigo-600'],
                ['label' => 'Giờ OT', 'value' => $summary['overtime_hours'].'h', 'tone' => 'text-violet-600'],
            ] as $card)
                @include('accountant.partials.stat-card', ['label' => $card['label'], 'value' => $card['value'], 'tone' => $card['tone']])
            @endforeach
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-emerald-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết bảng công — {{ $monthLabel }}</h3>
                <p class="text-xs text-slate-500">{{ $attendances->count() }} bản ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-emerald-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">STT</th>
                            <th class="px-4 py-3">Ngày</th>
                            <th class="px-4 py-3">Ca</th>
                            <th class="px-4 py-3">Check in</th>
                            <th class="px-4 py-3">Check out</th>
                            <th class="px-4 py-3 text-center">Muộn</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3 text-right">Giờ làm</th>
                            <th class="px-4 py-3 text-right">OT</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($attendances as $index => $attendance)
                            @php
                                $rowClass = match($attendance->status) {
                                    'late' => 'bg-amber-50/40',
                                    'absent' => 'bg-rose-50/40',
                                    'leave' => 'bg-sky-50/40',
                                    default => '',
                                };
                            @endphp
                            <tr class="hover:bg-emerald-50/30 {{ $rowClass }}">
                                <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-800">{{ $attendance->attendance_date->format('d/m/Y') }}</span>
                                    <span class="ml-1 text-xs text-slate-400">({{ $attendance->attendance_date->locale('vi')->isoFormat('ddd') }})</span>
                                </td>
                                <td class="px-4 py-3">{{ $attendance->employeeShift?->shift?->shift_name ?? $attendance->shift?->shift_name ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono">{{ $attendance->check_in?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono">{{ $attendance->check_out?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if(($attendance->late_minutes ?? 0) > 0)
                                        <span class="text-xs font-semibold text-amber-700">+{{ $attendance->late_minutes }}p</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">@include('accountant.attendance.partials.status-badge', ['attendance' => $attendance])</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $attendance->work_hours > 0 ? $attendance->work_hours.'h' : '—' }}</td>
                                <td class="px-4 py-3 text-right text-violet-700">{{ ($attendance->overtime_hours ?? 0) > 0 ? $attendance->overtime_hours.'h' : '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('accountant.attendance.show', $attendance) }}" class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-14 text-center text-slate-500">
                                    Không có dữ liệu chấm công tháng {{ $monthLabel }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($attendances->isNotEmpty())
                        <tfoot class="border-t-2 border-emerald-100 bg-emerald-50/50">
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Tổng</td>
                                <td class="px-4 py-3 text-right font-bold text-indigo-700">{{ $summary['total_hours'] }}h</td>
                                <td class="px-4 py-3 text-right font-bold text-violet-700">{{ $summary['overtime_hours'] }}h</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
