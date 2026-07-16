@php
    $statusClasses = ['active' => 'bg-emerald-100 text-emerald-700', 'inactive' => 'bg-amber-100 text-amber-700', 'resigned' => 'bg-slate-100 text-slate-600'];
    $statusLabels = ['active' => 'Đang làm việc', 'inactive' => 'Tạm nghỉ', 'resigned' => 'Đã nghỉ'];
    $attendanceLabels = ['present' => 'Đúng giờ', 'late' => 'Đi muộn', 'absent' => 'Vắng mặt', 'leave' => 'Nghỉ phép'];
    $leaveLabels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
@endphp

<x-leader-layout title="{{ $employee->full_name }}" subtitle="Hồ sơ nhân viên trong nhóm">
    <div class="leader-page">
        <section class="leader-hero">
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-white/30 bg-white/15 text-3xl font-extrabold backdrop-blur">
                        @if ($employee->avatar)
                            <img src="{{ asset('storage/' . $employee->avatar) }}" alt="{{ $employee->full_name }}" class="h-full w-full object-cover">
                        @else
                            {{ strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-violet-200">Hồ sơ thành viên nhóm</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $employee->full_name }}</h2>
                        <p class="mt-1 text-sm text-violet-100">
                            {{ $employee->employee_code }}
                            · {{ $employee->department?->department_name ?? '—' }}
                            · {{ $employee->position?->position_name ?? 'Chưa có chức vụ' }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('leader.employees.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-violet-700 shadow-lg hover:bg-violet-50">
                    ← Danh sách thành viên
                </a>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'KPI', 'value' => $kpiStats['total'], 'note' => $kpiStats['completed'].' hoàn thành', 'tone' => 'text-sky-600'])
            @include('leader.partials.stat-card', ['label' => 'TB tiến độ KPI', 'value' => $kpiStats['avg_progress'].'%', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Chấm công gần đây', 'value' => $attendances->count(), 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Đơn nghỉ', 'value' => $leaveRequests->count(), 'tone' => 'text-amber-600'])
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="leader-card xl:col-span-2">
                <div class="border-b border-violet-100/80 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Thông tin nhân viên</h3>
                    <p class="text-xs text-slate-500">Thông tin cơ bản và liên hệ</p>
                </div>
                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Mã nhân viên</p>
                        <p class="mt-2 font-bold text-slate-800">{{ $employee->employee_code }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Trạng thái</p>
                        <span class="leader-badge mt-2 {{ $statusClasses[$employee->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $statusLabels[$employee->status] ?? $employee->status }}
                        </span>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Email</p>
                        <p class="mt-2 font-semibold text-slate-700">{{ $employee->email ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Số điện thoại</p>
                        <p class="mt-2 font-semibold text-slate-700">{{ $employee->phone ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Ngày sinh</p>
                        <p class="mt-2 font-semibold text-slate-700">{{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Ngày vào làm</p>
                        <p class="mt-2 font-semibold text-slate-700">{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 md:col-span-2">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Địa chỉ</p>
                        <p class="mt-2 font-semibold text-slate-700">{{ $employee->address ?: 'Chưa cập nhật' }}</p>
                    </div>
                </div>
            </div>

            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Truy cập nhanh</h3>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('leader.kpis.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-violet-200 hover:bg-violet-50/50">
                        KPI của nhóm
                        <span class="text-violet-700">→</span>
                    </a>
                    <a href="{{ route('leader.team-schedule.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-violet-200 hover:bg-violet-50/50">
                        Lịch làm việc nhóm
                        <span class="text-violet-700">→</span>
                    </a>
                    <a href="{{ route('leader.team-requests.index', ['action' => 'remove', 'employee_id' => $employee->id]) }}" class="flex items-center justify-between rounded-xl border border-rose-100 px-4 py-3 text-sm font-medium text-rose-700 transition hover:bg-rose-50/50">
                        Đề xuất đưa ra khỏi nhóm
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">KPI được giao</h3>
                <div class="mt-4 space-y-3">
                    @forelse($kpis as $kpi)
                        <a href="{{ route('leader.kpis.show', $kpi) }}" class="block rounded-xl border border-violet-100 p-4 transition hover:bg-violet-50/40">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-800">{{ $kpi->kpi?->title ?? 'KPI' }}</p>
                                <span class="shrink-0 text-sm font-bold text-violet-700">{{ $kpi->progress }}%</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="text-slate-500">{{ $kpi->status_label }}</span>
                                @if($kpi->deadline)
                                    <span class="text-xs text-slate-400">Hạn: {{ $kpi->deadline->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500" style="width: {{ min(100, max(4, (int) $kpi->progress)) }}%"></div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Chưa có KPI.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="leader-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Chấm công gần đây</h3>
                    <div class="mt-4 space-y-2">
                        @forelse($attendances as $att)
                            <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-800">{{ $att->attendance_date?->format('d/m/Y') }}</span>
                                    <span class="leader-badge {{ $att->status === 'late' ? 'bg-amber-100 text-amber-700' : ($att->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $attendanceLabels[$att->status] ?? $att->status }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $att->shift?->shift_name ?? 'Ca làm' }}
                                    · Vào {{ $att->check_in?->format('H:i') ?? '--:--' }}
                                    · Ra {{ $att->check_out?->format('H:i') ?? '--:--' }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Chưa có dữ liệu chấm công.</p>
                        @endforelse
                    </div>
                </div>

                <div class="leader-card p-5">
                    <h3 class="text-sm font-bold text-slate-800">Đơn nghỉ gần đây</h3>
                    <div class="mt-4 space-y-2">
                        @forelse($leaveRequests as $leave)
                            <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                <p class="font-semibold text-slate-800">
                                    {{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $leave->reason ?: 'Không có lý do' }}
                                    · {{ $leaveLabels[$leave->status] ?? $leave->status }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Chưa có đơn nghỉ.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-leader-layout>
