<x-accountant-layout title="Chi tiết chấm công" subtitle="{{ $attendance->attendance_date?->format('d/m/Y') }}">
<div class="accountant-page">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            Chế độ chỉ xem — không thể chỉnh sửa dữ liệu chấm công.
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Chi tiết chấm công</h2>
                <p class="text-sm text-slate-500">
                    {{ $attendance->employee->full_name ?? '—' }}
                    · {{ $attendance->attendance_date?->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accountant.attendance.index', ['employee_id' => $attendance->employee_id, 'month' => $attendance->attendance_date?->format('Y-m')]) }}"
                   class="accountant-btn-secondary">← Bảng công NV</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Check in', 'value' => $attendance->check_in?->format('H:i') ?? '—', 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Check out', 'value' => $attendance->check_out?->format('H:i') ?? '—', 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Giờ làm', 'value' => ($attendance->work_hours ?? 0).'h', 'tone' => 'text-slate-800'])
            @include('accountant.partials.stat-card', ['label' => 'Giờ OT', 'value' => ($attendance->overtime_hours ?? 0).'h', 'tone' => 'text-violet-600'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="accountant-card p-5 sm:p-6">
                <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin nhân viên</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach([
                        ['label' => 'Họ tên', 'value' => $attendance->employee->full_name ?? '—'],
                        ['label' => 'Mã NV', 'value' => $attendance->employee->employee_code ?? '—'],
                        ['label' => 'Phòng ban', 'value' => $attendance->employee->department->department_name ?? '—'],
                        ['label' => 'Chức vụ', 'value' => $attendance->employee->position->position_name ?? '—'],
                    ] as $field)
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $field['label'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="accountant-card p-5 sm:p-6">
                <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin chấm công</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach([
                        ['label' => 'Ngày', 'value' => $attendance->attendance_date?->format('d/m/Y') ?? '—'],
                        ['label' => 'Ca làm', 'value' => $attendance->employeeShift?->shift?->shift_name ?? $attendance->shift?->shift_name ?? '—'],
                        ['label' => 'Trạng thái', 'value' => null],
                        ['label' => 'Đi muộn', 'value' => ($attendance->late_minutes ?? 0) > 0 ? $attendance->late_minutes.' phút' : 'Đúng giờ'],
                        ['label' => 'Phương thức vào', 'value' => $attendance->check_in_method_label],
                        ['label' => 'Phương thức ra', 'value' => $attendance->check_out_method_label],
                    ] as $field)
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $field['label'] }}</p>
                            @if($field['label'] === 'Trạng thái')
                                <div class="mt-1">@include('accountant.attendance.partials.status-badge', ['attendance' => $attendance])</div>
                            @else
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($attendance->morning_check_in || $attendance->afternoon_check_in)
            <div class="accountant-card p-5 sm:p-6">
                <h3 class="mb-4 text-sm font-bold text-slate-800">Chấm công theo buổi</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <p class="text-xs font-bold uppercase text-emerald-700">Buổi sáng</p>
                        <p class="mt-2 text-sm">Vào: <span class="font-mono font-semibold">{{ $attendance->morning_check_in?->format('H:i') ?? '—' }}</span></p>
                        <p class="text-sm">Ra: <span class="font-mono font-semibold">{{ $attendance->morning_check_out?->format('H:i') ?? '—' }}</span></p>
                        @if(($attendance->morning_late_minutes ?? 0) > 0)
                            <p class="mt-1 text-xs text-amber-700">Muộn {{ $attendance->morning_late_minutes }} phút</p>
                        @endif
                    </div>
                    <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                        <p class="text-xs font-bold uppercase text-indigo-700">Buổi chiều</p>
                        <p class="mt-2 text-sm">Vào: <span class="font-mono font-semibold">{{ $attendance->afternoon_check_in?->format('H:i') ?? '—' }}</span></p>
                        <p class="text-sm">Ra: <span class="font-mono font-semibold">{{ $attendance->afternoon_check_out?->format('H:i') ?? '—' }}</span></p>
                        @if(($attendance->afternoon_late_minutes ?? 0) > 0)
                            <p class="mt-1 text-xs text-amber-700">Muộn {{ $attendance->afternoon_late_minutes }} phút</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-accountant-layout>
