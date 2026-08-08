@php
    $displayName = Auth::user()->displayName();
    $attendanceLabels = ['present' => 'Đúng giờ', 'late' => 'Đi muộn', 'absent' => 'Vắng', 'leave' => 'Nghỉ phép'];
    $attendanceBadge = [
        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
        'late' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
        'absent' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
        'leave' => 'bg-sky-50 text-sky-700 ring-sky-600/10',
    ];
    $payrollLabels = ['open' => 'Đang mở', 'calculated' => 'Đã tính', 'approved' => 'Đã duyệt', 'paid' => 'Đã trả', 'closed' => 'Đóng', 'draft' => 'Nháp', 'pending' => 'Chờ duyệt'];
    $kpiLabels = ['pending' => 'Chờ', 'in_progress' => 'Đang làm', 'completed' => 'Hoàn thành', 'not_completed' => 'Chưa xong'];
    $completionRate = ($kpiSummary->total ?? 0) > 0 ? round((($kpiSummary->completed ?? 0) / max($kpiSummary->total, 1)) * 100) : 0;
    $pendingLeave = (int) ($leaveSummary->pending ?? 0);

    $stats = [
        ['value' => number_format((int) ($attendanceSummary->shifts_completed ?? 0)), 'label' => 'Ca làm tháng này', 'tone' => 'from-emerald-500 to-teal-500', 'bg' => 'bg-emerald-50'],
        ['value' => number_format((float) ($attendanceSummary->work_hours ?? 0), 1), 'label' => 'Giờ công', 'tone' => 'from-sky-500 to-blue-500', 'bg' => 'bg-sky-50'],
        ['value' => $pendingLeave, 'label' => 'Đơn nghỉ chờ duyệt', 'tone' => 'from-amber-500 to-orange-500', 'bg' => 'bg-amber-50'],
        ['value' => $completionRate . '%', 'label' => 'KPI hoàn thành', 'tone' => 'from-indigo-500 to-violet-500', 'bg' => 'bg-indigo-50'],
        ['value' => number_format($unreadNotifications), 'label' => 'Thông báo chưa đọc', 'tone' => 'from-rose-500 to-pink-500', 'bg' => 'bg-rose-50'],
    ];
@endphp

<x-employee-layout title="Trang chủ" :subtitle="null">
    <div class="employee-page w-full">
        {{-- Banner chào --}}
        <section class="employee-welcome relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 via-blue-600 to-indigo-600 px-5 py-5 text-white shadow-lg shadow-sky-500/20 sm:px-6 sm:py-6">
            <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-sky-100">Xin chào,</p>
                    <h2 class="text-2xl font-bold tracking-tight">{{ $displayName }}</h2>
                    <p class="mt-1 text-sm text-sky-100/90">
                        {{ $employeeProfile?->employee_code ?? '—' }}
                        · {{ $employeeProfile?->position_name ?? 'Nhân viên' }}
                        · {{ $employeeProfile?->department_name ?? 'Chưa gán phòng ban' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('attendance.index') }}" class="inline-flex items-center rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur transition hover:bg-white/25">Chấm công</a>
                    <a href="{{ route('employee.payrolls.index') }}" class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-sky-700 shadow-sm transition hover:bg-sky-50">Bảng lương</a>
                </div>
            </div>
        </section>

        @if (! $employeeProfile)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Tài khoản chưa liên kết hồ sơ nhân sự.
                <a href="{{ route('profile.edit') }}" class="font-semibold text-amber-900 underline">Cập nhật hồ sơ</a>
            </div>
        @endif

        {{-- Stats full width --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            @foreach ($stats as $stat)
                <div class="employee-stat-card group">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $stat['tone'] }} text-sm font-bold text-white shadow-sm">
                            {{ mb_substr($stat['label'], 0, 1) }}
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-bold tabular-nums tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Nội dung chính full width --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            {{-- Chấm công --}}
            <section class="employee-panel xl:col-span-7">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Chấm công gần đây</h3>
                        <p class="text-xs text-slate-500">Lịch sử ca làm 7 ngày gần nhất</p>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Xem tất cả →</a>
                </div>
                @if ($attendanceHistory->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-slate-400">Chưa có dữ liệu chấm công.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/80 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-3">Ngày</th>
                                    <th class="px-5 py-3">Check-in</th>
                                    <th class="px-5 py-3">Check-out</th>
                                    <th class="px-5 py-3">Giờ</th>
                                    <th class="px-5 py-3 text-right">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($attendanceHistory as $attendance)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-5 py-3 font-medium text-slate-800">{{ \Illuminate\Support\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ $attendance->check_in ? \Illuminate\Support\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ $attendance->check_out ? \Illuminate\Support\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}</td>
                                        <td class="px-5 py-3 text-slate-600">{{ number_format((float) ($attendance->work_hours ?? 0), 1) }}h</td>
                                        <td class="px-5 py-3 text-right">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $attendanceBadge[$attendance->status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/10' }}">
                                                {{ $attendanceLabels[$attendance->status] ?? $attendance->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Cột phải --}}
            <div class="space-y-5 xl:col-span-5">
                <section class="employee-panel overflow-hidden">
                    <div class="bg-gradient-to-br from-sky-500 to-indigo-600 px-5 py-5 text-white">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-100">Lương gần nhất</p>
                        @if ($latestPayroll)
                            <p class="mt-2 text-3xl font-bold tabular-nums">{{ number_format((float) $latestPayroll->total_salary, 0, ',', '.') }}₫</p>
                            <p class="mt-1 text-sm text-sky-100">Kỳ {{ str_pad((string) $latestPayroll->month, 2, '0', STR_PAD_LEFT) }}/{{ $latestPayroll->year }} · {{ $payrollLabels[$latestPayroll->status] ?? $latestPayroll->status }}</p>
                        @else
                            <p class="mt-3 text-lg font-semibold">Chưa có phiếu lương</p>
                        @endif
                    </div>
                    @if ($latestPayroll)
                        <div class="space-y-2 px-5 py-4 text-sm">
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Lương CB</span><span class="font-semibold text-slate-800">{{ number_format((float) $latestPayroll->basic_salary, 0, ',', '.') }}₫</span></div>
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Phụ cấp</span><span class="font-semibold text-slate-800">{{ number_format((float) $latestPayroll->allowance, 0, ',', '.') }}₫</span></div>
                            <a href="{{ route('employee.payrolls.index') }}" class="mt-2 block text-center text-xs font-semibold text-sky-600 hover:text-sky-800">Xem chi tiết phiếu lương →</a>
                        </div>
                    @endif
                </section>

                <section class="employee-panel">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h3 class="text-base font-bold text-slate-900">Thông báo</h3>
                        <a href="{{ route('employee.notifications.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Tất cả</a>
                    </div>
                    <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto">
                        @forelse ($notifications as $notification)
                            <div class="flex gap-3 px-5 py-3 {{ $notification->is_read ? '' : 'bg-sky-50/50' }}">
                                @if (! $notification->is_read)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                                @else
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent"></span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-800">{{ $notification->title }}</p>
                                    <p class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-8 text-center text-sm text-slate-400">Không có thông báo.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- KPI full row bottom on wide screens spans 7+5 --}}
            <section class="employee-panel xl:col-span-7">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tiến độ KPI</h3>
                        <p class="text-xs text-slate-500">Trung bình {{ round($kpiSummary->average_progress ?? 0) }}% hoàn thành</p>
                    </div>
                    <a href="{{ route('employee.kpis.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Quản lý KPI →</a>
                </div>
                @if ($kpiItems->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-slate-400">Chưa có KPI được giao.</p>
                @else
                    <div class="grid gap-0 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 xl:grid-cols-1 xl:divide-y">
                        @foreach ($kpiItems as $item)
                            @php $progress = min(100, max(0, (int) ($item->progress ?? 0))); @endphp
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800">{{ $item->title }}</p>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $kpiLabels[$item->status] ?? $item->status }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $progress }}%"></div>
                                </div>
                                <p class="mt-1.5 text-xs font-medium text-sky-700">{{ $progress }}% hoàn thành</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Quick links fill right bottom --}}
            <section class="employee-panel xl:col-span-5">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-base font-bold text-slate-900">Truy cập nhanh</h3>
                </div>
                <div class="grid grid-cols-2 gap-2 p-4">
                    @foreach([
                        ['Nghỉ phép', route('employee.leave-requests'), 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['Tăng ca', route('employee.overtime-requests'), 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Về sớm', route('employee.early-leave.index'), 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15'],
                        ['Khiếu nại lương', route('employee.payroll-complaints.index'), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Hợp đồng', route('employee.contracts.index'), 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                        ['Hồ sơ cá nhân', route('profile.edit'), 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                    ] as [$label, $href, $icon])
                        <a href="{{ $href }}" class="flex items-center gap-2.5 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3 text-sm font-medium text-slate-700 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-800">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-sky-600 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                            </span>
                            <span class="truncate">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-employee-layout>
