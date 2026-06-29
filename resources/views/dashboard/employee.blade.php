@php
    $navigation = [
        ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
        ['label' => 'Chấm công', 'href' => route('employee.dashboard') . '#attendance', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lịch sử gần đây'],
        ['label' => 'KPI', 'href' => route('employee.kpis.index'), 'route' => 'employee.kpis.*', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'note' => 'Mục tiêu công việc'],
        ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
        ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
        ['label' => 'Thông báo', 'href' => route('employee.dashboard') . '#notices', 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'note' => 'Tin nội bộ'],
        ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
    ];
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
    $employeeName = $employeeProfile?->full_name ?? Auth::user()->name;
    $attendanceLabels = ['present' => 'Đúng giờ', 'late' => 'Đi muộn', 'absent' => 'Vắng mặt', 'leave' => 'Nghỉ phép'];
    $attendanceClasses = ['present' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'late' => 'bg-amber-50 text-amber-700 border-amber-100', 'absent' => 'bg-rose-50 text-rose-700 border-rose-100', 'leave' => 'bg-sky-50 text-sky-700 border-sky-100'];
    $payrollLabels = [
        'open' => 'Đang mở',
        'calculated' => 'Đã tính lương',
        'approved' => 'Đã duyệt',
        'paid' => 'Đã thanh toán',
        'closed' => 'Đã đóng',
        'draft' => 'Nháp',
        'pending' => 'Chờ duyệt',
    ];
    $payrollClasses = [
        'open' => 'bg-slate-50 text-slate-700 border-slate-200',
        'calculated' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'closed' => 'bg-slate-50 text-slate-600 border-slate-200',
        'draft' => 'bg-amber-50 text-amber-700 border-amber-100',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
    ];
    $kpiLabels = ['pending' => 'Chờ bắt đầu', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành', 'not_completed' => 'Không hoàn thành'];
    $kpiClasses = ['pending' => 'bg-amber-50 text-amber-700 border-amber-100', 'in_progress' => 'bg-cyan-50 text-cyan-700 border-cyan-100', 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'not_completed' => 'bg-rose-50 text-rose-700 border-rose-100'];
    $noticeLabels = ['system' => 'Hệ thống', 'leave' => 'Nghỉ phép', 'payroll' => 'Lương', 'kpi' => 'KPI'];
    $noticeClasses = ['system' => 'bg-slate-100 text-slate-700', 'leave' => 'bg-amber-100 text-amber-700', 'payroll' => 'bg-emerald-100 text-emerald-700', 'kpi' => 'bg-sky-100 text-sky-700'];
    $completionRate = ($kpiSummary->total ?? 0) > 0 ? round((($kpiSummary->completed ?? 0) / max($kpiSummary->total, 1)) * 100) : 0;
@endphp

<x-staff-layout title="Employee Dashboard" subtitle="Chấm công, KPI, lương và thông báo đều được gom về một nơi." role="employee" :navigation="$navigation">
    <section class="relative mb-8 overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-600 p-6 text-white shadow-xl shadow-sky-500/20 sm:p-8">
        <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-40 w-40 -translate-x-1/4 translate-y-1/4 rounded-full bg-sky-300/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur"><span class="h-2 w-2 rounded-full bg-white"></span> Không gian làm việc cá nhân</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Chào {{ $firstName }}, mọi cập nhật công việc của bạn đều có mặt tại đây.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-sky-50/90 sm:text-base">Dashboard này ưu tiên tốc độ theo dõi hằng ngày: bạn có thể xem lịch sử chấm công, tiến độ KPI, bảng lương và tin nội bộ mà không phải chuyển nhiều màn hình.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="#attendance" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-sky-700 shadow-lg shadow-sky-900/10 transition hover:-translate-y-0.5">Xem chấm công</a>
                    <a href="#payroll" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">Mở bảng lương</a>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 xl:w-[440px] xl:grid-cols-1">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-100">Mã nhân viên</p><p class="mt-2 text-lg font-bold">{{ $employeeProfile?->employee_code ?? 'Chưa cập nhật' }}</p><p class="mt-1 text-sm text-sky-50/85">{{ $employeeProfile?->position_name ?? 'Nhân viên' }}</p></div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-100">Phòng ban</p><p class="mt-2 text-lg font-bold">{{ $employeeProfile?->department_name ?? 'Chưa gán phòng ban' }}</p><p class="mt-1 text-sm text-sky-50/85">{{ $employeeProfile?->email ?? Auth::user()->email }}</p></div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-100">Thông báo mới</p><p class="mt-2 text-lg font-bold">{{ number_format($unreadNotifications) }}</p><p class="mt-1 text-sm text-sky-50/85">Việc cần đọc trong ngày</p></div>
            </div>
        </div>
    </section>

    @if (! $employeeProfile)
        <div class="staff-card mb-8 border border-amber-100 bg-amber-50/90 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div><h3 class="text-base font-bold text-amber-800">Tài khoản employee chưa liên kết hồ sơ nhân sự</h3><p class="mt-1 text-sm text-amber-700">Để dashboard hiển thị đầy đủ chấm công, KPI và lương, tài khoản này cần được gắn với bản ghi trong bảng `employees`.</p></div>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">Cập nhật hồ sơ</a>
            </div>
        </div>
    @endif

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="staff-stat-card border border-emerald-100/80 bg-white/90"><div class="flex items-start justify-between"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-emerald-700">Month</span></div><p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format((int) ($attendanceSummary->shifts_completed ?? 0)) }}</p><p class="mt-1 text-sm font-medium text-slate-500">Ca làm tháng này</p></div>
        <div class="staff-stat-card border border-cyan-100/80 bg-white/90"><div class="flex items-start justify-between"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-lg shadow-cyan-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0l3-3m-3 3L9 9m12 3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><span class="rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-700">Hours</span></div><p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format((float) ($attendanceSummary->work_hours ?? 0), 1) }}</p><p class="mt-1 text-sm font-medium text-slate-500">Tổng giờ công</p></div>
        <div class="staff-stat-card border border-amber-100/80 bg-white/90"><div class="flex items-start justify-between"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z" /></svg></div><span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Leave</span></div><p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format((int) ($leaveSummary->pending ?? 0)) }}</p><p class="mt-1 text-sm font-medium text-slate-500">Đơn nghỉ chờ phản hồi</p></div>
        <div class="staff-stat-card border border-sky-100/80 bg-white/90"><div class="flex items-start justify-between"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-lg shadow-sky-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg></div><span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-sky-700">KPI</span></div><p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ $completionRate }}%</p><p class="mt-1 text-sm font-medium text-slate-500">KPI đã hoàn thành</p></div>
        <div class="staff-stat-card border border-indigo-100/80 bg-white/90"><div class="flex items-start justify-between"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg></div><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-indigo-700">Inbox</span></div><p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($unreadNotifications) }}</p><p class="mt-1 text-sm font-medium text-slate-500">Thông báo chưa đọc</p></div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="attendance" class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Attendance</p><h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Lịch sử chấm công gần đây</h3><p class="mt-1 text-sm text-slate-500">Theo dõi ca làm, thời gian vào ra và trạng thái công gần nhất.</p></div>
                <div class="px-6 py-5 sm:px-7">
                    @if ($attendanceHistory->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center"><p class="text-sm font-semibold text-slate-700">Chưa có dữ liệu chấm công cho tài khoản này.</p><p class="mt-1 text-sm text-slate-500">Khi có lịch sử ca làm, thông tin sẽ hiển thị tại đây.</p></div>
                    @else
                        <div class="space-y-3">
                            @foreach ($attendanceHistory as $attendance)
                                <div class="rounded-3xl border border-slate-100 bg-white p-4 shadow-sm shadow-slate-100">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div><p class="font-semibold text-slate-800">{{ $attendance->shift_name ?? 'Ca làm việc' }} <span class="ml-2 text-sm font-medium text-slate-400">{{ \Illuminate\Support\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</span></p><p class="mt-1 text-sm text-slate-500">Check-in: {{ $attendance->check_in ? \Illuminate\Support\Carbon::parse($attendance->check_in)->format('H:i') : '--:--' }} · Check-out: {{ $attendance->check_out ? \Illuminate\Support\Carbon::parse($attendance->check_out)->format('H:i') : '--:--' }}</p></div>
                                        <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $attendanceClasses[$attendance->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ $attendanceLabels[$attendance->status] ?? ucfirst($attendance->status) }}</span><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ number_format((float) ($attendance->work_hours ?? 0), 1) }} giờ</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section id="kpi" class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7"><div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">KPI tracker</p><h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Tiến độ mục tiêu cá nhân</h3><p class="mt-1 text-sm text-slate-500">Xem mức độ hoàn thành và điểm số mới nhất cho từng KPI.</p></div><div class="rounded-3xl bg-slate-50 px-4 py-3 text-right"><p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Tiến độ trung bình</p><p class="text-2xl font-bold text-slate-800">{{ round($kpiSummary->average_progress ?? 0) }}%</p></div></div></div>
                <div class="px-6 py-5 sm:px-7">
                    @if ($kpiItems->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center"><p class="text-sm font-semibold text-slate-700">Bạn chưa được giao KPI nào trong hệ thống.</p><p class="mt-1 text-sm text-slate-500">Khi có KPI mới, progress bar sẽ xuất hiện ngay tại đây.</p></div>
                    @else
                        <div class="space-y-4">
                            @foreach ($kpiItems as $item)
                                @php
                                    $progressWidth = min(100, max(4, (int) ($item->progress ?? 0)));
                                @endphp
                                <div class="rounded-3xl border border-slate-100 p-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div><p class="font-semibold text-slate-800">{{ $item->title }}</p><p class="mt-1 text-sm text-slate-500">Cập nhật {{ \Illuminate\Support\Carbon::parse($item->updated_at)->format('d/m/Y H:i') }}</p></div>
                                        <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $kpiClasses[$item->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ $kpiLabels[$item->status] ?? ucfirst($item->status) }}</span>@if (! is_null($item->score))<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Điểm {{ number_format((float) $item->score, 1) }}</span>@endif</div>
                                    </div>
                                    <div class="mt-4 h-2.5 rounded-full bg-slate-100">
                                        <div class="h-2.5 rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" @style(['width: ' . $progressWidth . '%'])></div>
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-sky-700">{{ (int) $item->progress }}% hoàn thành</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section id="payroll" class="staff-card overflow-hidden">
                <div class="bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-600 px-6 py-6 text-white"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-100">Payroll snapshot</p><h3 class="mt-2 text-2xl font-bold">Bảng lương gần nhất</h3>@if ($latestPayroll)<p class="mt-3 text-4xl font-extrabold tracking-tight">{{ number_format((float) $latestPayroll->total_salary, 0, ',', '.') }}đ</p><p class="mt-2 text-sm text-sky-50/85">Kỳ lương {{ str_pad((string) $latestPayroll->month, 2, '0', STR_PAD_LEFT) }}/{{ $latestPayroll->year }}</p>@else<p class="mt-3 text-lg font-semibold text-sky-50">Chưa có dữ liệu lương</p><p class="mt-2 text-sm text-sky-50/85">Phiếu lương sẽ xuất hiện tại đây sau khi hệ thống tính lương.</p>@endif</div>
                <div class="px-6 py-5">
                    @if ($latestPayroll)
                        <div class="mb-4"><span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $payrollClasses[$latestPayroll->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ $payrollLabels[$latestPayroll->status] ?? ucfirst($latestPayroll->status) }}</span></div>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Lương cơ bản</span><span class="font-semibold text-slate-800">{{ number_format((float) $latestPayroll->basic_salary, 0, ',', '.') }}đ</span></div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Phụ cấp</span><span class="font-semibold text-slate-800">{{ number_format((float) $latestPayroll->allowance, 0, ',', '.') }}đ</span></div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Thưởng / khấu trừ</span><span class="font-semibold text-slate-800">{{ number_format((float) $latestPayroll->bonus, 0, ',', '.') }}đ / {{ number_format((float) $latestPayroll->deduction, 0, ',', '.') }}đ</span></div>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('employee.payrolls.index') }}" class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Xem tất cả phiếu lương</a>
                            <a href="{{ route('employee.payrolls.pdf', ['payroll' => $latestPayroll->id ?? 0]) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Tải phiếu hiện tại</a>
                        </div>
                    @else
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center"><p class="text-sm font-semibold text-slate-700">Chưa có phiếu lương để hiển thị.</p></div>
                    @endif
                </div>
            </section>

            <section class="staff-card p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Hồ sơ cá nhân</p><h3 class="mt-2 text-xl font-bold text-slate-800">Thông tin nhanh</h3>
                <div class="mt-6 space-y-4">
                    <div class="rounded-3xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Họ tên</p><p class="mt-2 text-base font-bold text-slate-800">{{ $employeeName }}</p></div>
                    <div class="rounded-3xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Vai trò & phòng ban</p><p class="mt-2 text-base font-bold text-slate-800">{{ $employeeProfile?->position_name ?? 'Nhân viên' }}</p><p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->department_name ?? 'Chưa gán phòng ban' }}</p></div>
                    <div class="rounded-3xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Liên hệ</p><p class="mt-2 text-sm font-semibold text-slate-700">{{ $employeeProfile?->email ?? Auth::user()->email }}</p><p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->phone ?? 'Chưa cập nhật số điện thoại' }}</p></div>
                    <div class="rounded-3xl bg-slate-50 p-4"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Hợp đồng / ngày vào làm</p><p class="mt-2 text-sm font-semibold text-slate-700">{{ $contract?->contract_code ?? 'Chưa có hợp đồng' }}</p><p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->hire_date ? \Illuminate\Support\Carbon::parse($employeeProfile->hire_date)->format('d/m/Y') : 'Chưa cập nhật ngày vào làm' }}</p></div>
                </div>
            </section>

            <section id="notices" class="staff-card p-6">
                <div class="flex items-start justify-between gap-3"><div><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Notifications</p><h3 class="mt-2 text-xl font-bold text-slate-800">Thông báo mới</h3><p class="mt-1 text-sm text-slate-500">Những cập nhật gần nhất dành cho bạn.</p></div><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ number_format($unreadNotifications) }} chưa đọc</span></div>
                @if ($notifications->isEmpty())
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center"><p class="text-sm font-semibold text-slate-700">Hiện chưa có thông báo nào.</p></div>
                @else
                    <div class="mt-6 space-y-3">
                        @foreach ($notifications as $notification)
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div><p class="font-semibold text-slate-800">{{ $notification->title }}</p><p class="mt-1 text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}</p></div>
                                    <div class="flex items-center gap-2"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $noticeClasses[$notification->type] ?? 'bg-slate-100 text-slate-700' }}">{{ $noticeLabels[$notification->type] ?? ucfirst($notification->type) }}</span>@if (! $notification->is_read)<span class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-500"></span>@endif</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-staff-layout>
