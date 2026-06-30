@php
    $navigation = [
        ['label' => 'Dashboard', 'href' => route('manager.dashboard'), 'route' => 'manager.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Tổng quan điều hành'],
        ['label' => 'Đội ngũ', 'href' => route('manager.employees.index'), 'route' => 'manager.employees.*', 'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', 'note' => 'Nhân viên phòng ban'],
        ['label' => 'Phê duyệt', 'href' => route('manager.dashboard') . '#approvals', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Đơn nghỉ đang chờ'],
        ['label' => 'KPI', 'href' => route('manager.dashboard') . '#kpi', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'note' => 'Tiến độ phòng ban'],
        ['label' => 'Nghỉ phép', 'href' => route('manager.leave-requests.index'), 'route' => 'manager.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Quản lý nghỉ phép'],
        ['label' => 'Thông báo', 'href' => route('manager.notifications.index'), 'route' => 'manager.notifications*', 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'note' => 'Phòng ban của bạn'],
        ['label' => 'Tuyển dụng', 'href' => route('manager.dashboard') . '#recruitment', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'note' => 'Tin tuyển đang mở'],
        ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin tài khoản'],
    ];

    $statusClasses = ['active' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'inactive' => 'bg-slate-100 text-slate-600 border-slate-200', 'resigned' => 'bg-rose-50 text-rose-700 border-rose-100'];
    $statusLabels = ['active' => 'Đang làm việc', 'inactive' => 'Tạm ngưng', 'resigned' => 'Đã nghỉ'];
    $attendanceLabels = ['present' => 'Đúng giờ', 'late' => 'Đi muộn', 'absent' => 'Vắng mặt', 'leave' => 'Nghỉ phép'];
    $leaveLabels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
    $kpiLabels = ['pending' => 'Chờ bắt đầu', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành'];
@endphp

<x-staff-layout title="Chi tiết nhân viên" subtitle="Xem hồ sơ nhân viên thuộc phòng ban bạn quản lý." role="manager" :navigation="$navigation">
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 p-6 text-white shadow-xl shadow-emerald-500/20 sm:p-8">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
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
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-emerald-100">Hồ sơ đội ngũ</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $employee->full_name }}</h2>
                        <p class="mt-1 text-sm text-emerald-50">{{ $employee->employee_code }} · {{ $employee->position?->position_name ?? 'Chưa có chức vụ' }}</p>
                    </div>
                </div>
                <a href="{{ route('manager.employees.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-emerald-700 shadow-lg shadow-emerald-900/10 hover:bg-emerald-50">← Danh sách đội ngũ</a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="staff-card overflow-hidden xl:col-span-2">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-xl font-bold text-slate-800">Thông tin nhân viên</h3>
                    <p class="mt-1 text-sm text-slate-500">Thông tin cơ bản, liên hệ và công việc.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Mã nhân viên</p><p class="mt-2 font-bold text-slate-800">{{ $employee->employee_code }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Trạng thái</p><span class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$employee->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">{{ $statusLabels[$employee->status] ?? ucfirst($employee->status) }}</span></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Phòng ban</p><p class="mt-2 font-bold text-slate-800">{{ $employee->department?->department_name ?? '—' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Chức vụ</p><p class="mt-2 font-bold text-slate-800">{{ $employee->position?->position_name ?? '—' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Email</p><p class="mt-2 font-semibold text-slate-700">{{ $employee->email }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Số điện thoại</p><p class="mt-2 font-semibold text-slate-700">{{ $employee->phone }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Ngày sinh</p><p class="mt-2 font-semibold text-slate-700">{{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Ngày vào làm</p><p class="mt-2 font-semibold text-slate-700">{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Địa chỉ</p><p class="mt-2 font-semibold text-slate-700">{{ $employee->address ?: 'Chưa cập nhật' }}</p></div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="staff-card p-6">
                    <h3 class="text-lg font-bold text-slate-800">Tóm tắt</h3>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-emerald-50 p-4 text-center"><p class="text-2xl font-extrabold text-emerald-700">{{ $attendances->count() }}</p><p class="mt-1 text-xs font-semibold text-emerald-700">Chấm công gần đây</p></div>
                        <div class="rounded-2xl bg-sky-50 p-4 text-center"><p class="text-2xl font-extrabold text-sky-700">{{ $kpis->count() }}</p><p class="mt-1 text-xs font-semibold text-sky-700">KPI</p></div>
                        <div class="rounded-2xl bg-amber-50 p-4 text-center"><p class="text-2xl font-extrabold text-amber-700">{{ $leaveRequests->count() }}</p><p class="mt-1 text-xs font-semibold text-amber-700">Đơn nghỉ</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-extrabold text-slate-700">{{ $contracts->count() }}</p><p class="mt-1 text-xs font-semibold text-slate-600">Hợp đồng</p></div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5"><h3 class="text-xl font-bold text-slate-800">Chấm công gần đây</h3></div>
                <div class="p-6">
                    @forelse ($attendances as $attendance)
                        <div class="mb-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-800">{{ $attendance->attendance_date?->format('d/m/Y') }} · {{ $attendance->shift?->shift_name ?? 'Ca làm' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Vào: {{ $attendance->check_in?->format('H:i') ?? '--:--' }} · Ra: {{ $attendance->check_out?->format('H:i') ?? '--:--' }} · {{ $attendanceLabels[$attendance->status] ?? ucfirst($attendance->status) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Chưa có dữ liệu chấm công.</p>
                    @endforelse
                </div>
            </section>

            <section class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5"><h3 class="text-xl font-bold text-slate-800">Đơn nghỉ gần đây</h3></div>
                <div class="p-6">
                    @forelse ($leaveRequests as $leave)
                        <div class="mb-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="font-semibold text-slate-800">{{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $leave->reason ?: 'Không có lý do' }} · {{ $leaveLabels[$leave->status] ?? ucfirst($leave->status) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Chưa có đơn nghỉ nào.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="staff-card overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5"><h3 class="text-xl font-bold text-slate-800">KPI được giao</h3></div>
            <div class="p-6">
                @forelse ($kpis as $item)
                    <div class="mb-4 rounded-2xl border border-slate-100 p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-semibold text-slate-800">{{ $item->kpi?->title ?? 'KPI' }}</p><p class="mt-1 text-sm text-slate-500">{{ $kpiLabels[$item->status] ?? ucfirst($item->status) }}</p></div>
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">{{ (int) $item->progress }}%</span>
                        </div>
                        @php
                            $progressWidth = min(100, max(4, (int) ($item->progress ?? 0)));
                        @endphp
                        <div class="mt-3 h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" @style(['width: ' . $progressWidth . '%'])></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Chưa có KPI nào.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-staff-layout>
