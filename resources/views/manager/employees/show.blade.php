@php
    $statusClasses = ['active' => 'bg-blue-50 text-blue-700 border-blue-100', 'inactive' => 'bg-slate-100 text-slate-600 border-slate-200', 'resigned' => 'bg-rose-50 text-rose-700 border-rose-100'];
    $statusLabels = ['active' => 'Đang làm việc', 'inactive' => 'Tạm ngưng', 'resigned' => 'Đã nghỉ'];
    $attendanceLabels = ['present' => 'Đúng giờ', 'late' => 'Đi muộn', 'absent' => 'Vắng mặt', 'leave' => 'Nghỉ phép'];
    $leaveLabels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
    $kpiLabels = ['pending' => 'Chờ bắt đầu', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành'];
@endphp

<x-manager-layout title="Chi tiết nhân viên" subtitle="Xem hồ sơ nhân viên thuộc phòng ban bạn quản lý.">
    <div class="manager-page">
        <section class="manager-hero">
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
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-teal-200">Hồ sơ đội ngũ</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $employee->full_name }}</h2>
                        <p class="mt-1 text-sm text-teal-100">{{ $employee->employee_code }} · {{ $employee->position?->position_name ?? 'Chưa có chức vụ' }}</p>
                    </div>
                </div>
                <a href="{{ route('manager.employees.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-teal-700 shadow-lg shadow-teal-900/10 hover:bg-teal-50">← Danh sách đội ngũ</a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="manager-panel xl:col-span-2">
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
                <section class="manager-card p-6">
                    <h3 class="text-lg font-bold text-slate-800">Tóm tắt</h3>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-teal-50 p-4 text-center"><p class="text-2xl font-extrabold text-teal-700">{{ $attendances->count() }}</p><p class="mt-1 text-xs font-semibold text-teal-700">Chấm công gần đây</p></div>
                        <div class="rounded-2xl bg-sky-50 p-4 text-center"><p class="text-2xl font-extrabold text-sky-700">{{ $kpis->count() }}</p><p class="mt-1 text-xs font-semibold text-sky-700">KPI</p></div>
                        <div class="rounded-2xl bg-amber-50 p-4 text-center"><p class="text-2xl font-extrabold text-amber-700">{{ $leaveRequests->count() }}</p><p class="mt-1 text-xs font-semibold text-amber-700">Đơn nghỉ</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4 text-center"><p class="text-2xl font-extrabold text-slate-700">{{ $contracts->count() }}</p><p class="mt-1 text-xs font-semibold text-slate-600">Hợp đồng</p></div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="manager-panel">
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

            <section class="manager-panel">
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

        <section class="manager-card overflow-hidden">
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
                            <div class="h-2.5 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500" @style(['width: ' . $progressWidth . '%'])></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Chưa có KPI nào.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-manager-layout>
