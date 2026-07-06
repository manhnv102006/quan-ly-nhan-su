@php
    $navigation = \App\Support\ManagerNavigation::items();
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-blue-50 text-blue-700 border-blue-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
    $leaveTypes = [
        'annual' => ['label' => 'Nghỉ phép', 'class' => 'bg-sky-50 text-sky-700 border-sky-100'],
        'sick' => ['label' => 'Nghỉ ốm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'unpaid' => ['label' => 'Không lương', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
    ];
@endphp

<x-staff-layout
    title="Quản lý nghỉ phép"
    subtitle="Duyệt đơn nhân viên cấp dưới và quản lý đơn nghỉ phép cá nhân của bạn."
    role="manager"
    :navigation="$navigation"
>
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Nghỉ phép</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">Quản lý nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Duyệt đơn của <span class="font-semibold text-slate-700">nhân viên</span>.
                    Đơn của bạn do <span class="font-semibold text-slate-700">Admin</span> phê duyệt.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('employee.leave-requests') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Đơn của tôi
                </a>
                <a href="{{ route('employee.leave-requests.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Tạo đơn nghỉ phép
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4 shadow-sm">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100">
                    <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-violet-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        @endif

        @if(!($managerLinked ?? true))
            <div class="staff-card border border-amber-100 bg-amber-50/90 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-amber-800">Chưa liên kết hồ sơ nhân viên</h3>
                        <p class="mt-2 text-sm leading-6 text-amber-700">
                            Tài khoản quản lý của bạn chưa được liên kết với hồ sơ nhân viên trong hệ thống,
                            nên không thể tải danh sách đơn nghỉ phép cần duyệt.
                        </p>
                        <p class="mt-2 text-xs text-amber-600/90">
                            Vui lòng liên hệ quản trị viên để gán <code class="rounded bg-amber-100 px-1">Trưởng phòng ban</code>
                            trong mục Quản lý phòng ban. Chỉ quản lý được gán trưởng phòng mới nhận đơn nghỉ phép của nhân viên cùng phòng ban.
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        @else
            @if ($myLeaveStats)
                <section class="staff-card border border-sky-100/80 bg-sky-50/40 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Đơn cá nhân</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-800">Nghỉ phép của bạn</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Tổng {{ number_format($myLeaveStats['total']) }} đơn
                                @if ($myLeaveStats['pending'] > 0)
                                    · <span class="font-semibold text-amber-700">{{ number_format($myLeaveStats['pending']) }} đơn đang chờ Admin duyệt</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('employee.leave-requests') }}"
                               class="inline-flex items-center rounded-xl border border-sky-200 bg-white px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                                Xem đơn của tôi
                            </a>
                            <a href="{{ route('employee.leave-requests.create') }}"
                               class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                                Tạo đơn mới
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="staff-stat-card border border-amber-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Chờ</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['pending']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đơn nhân viên chờ duyệt</p>
                </div>

                <div class="staff-stat-card border border-violet-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-violet-700">OK</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['approved']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đã duyệt</p>
                </div>

                <div class="staff-stat-card border border-rose-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-lg shadow-rose-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-rose-700">Từ chối</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['rejected']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đã từ chối</p>
                </div>
            </section>

            <section class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Bộ lọc</p>
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Tìm kiếm đơn nghỉ phép</h3>
                </div>
                <div class="px-6 py-5 sm:px-7">
                    <form method="GET" action="{{ route('manager.leave-requests.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tên nhân viên</label>
                            <input type="text" name="employee_name" value="{{ $filters['employee_name'] ?? '' }}"
                                   placeholder="Nhập tên nhân viên"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mã nhân viên</label>
                            <input type="text" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}"
                                   placeholder="Nhập mã nhân viên"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Loại nghỉ</label>
                            <select name="leave_type" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                                <option value="">Tất cả</option>
                                @foreach(\App\Models\LeaveRequest::LEAVE_TYPE_LABELS as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['leave_type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Trạng thái</label>
                            <select name="status" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                                <option value="">Tất cả</option>
                                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Chờ duyệt</option>
                                <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Đã duyệt</option>
                                <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Từ chối</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nghỉ từ ngày</label>
                            <input type="date" name="start_from" value="{{ $filters['start_from'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nghỉ đến ngày</label>
                            <input type="date" name="start_to" value="{{ $filters['start_to'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div class="flex flex-wrap items-end justify-end gap-2 md:col-span-2 xl:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-900/20 transition hover:bg-violet-700">
                                Tìm kiếm
                            </button>
                            @if(collect($filters)->filter()->isNotEmpty())
                                <a href="{{ route('manager.leave-requests.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Xóa lọc
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </section>

            <section class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Danh sách</p>
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Đơn nghỉ phép nhân viên</h3>
                    <p class="mt-1 text-xs text-slate-500">Chỉ hiển thị đơn của nhân viên thường thuộc quyền quản lý</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">#</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Loại</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Số ngày</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($leaveRequests as $index => $item)
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ ($leaveRequests->firstItem() ?? 0) + $index }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $leaveTypes[$item->leave_type]['class'] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ \App\Models\LeaveRequest::LEAVE_TYPE_LABELS[$item->leave_type] ?? $item->leave_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        {{ optional($item->start_date)->format('d/m/Y') }} → {{ optional($item->end_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">{{ $item->total_days }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('manager.leave-requests.show', $item) }}"
                                           class="inline-flex items-center rounded-lg border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Không có đơn nghỉ phép phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($leaveRequests->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            </section>

            @include('manager.leave-requests.partials.history-table', [
                'histories' => $recentHistories,
                'showEmployee' => true,
                'showLeaveRequestLink' => true,
            ])
        @endif
    </div>
</x-staff-layout>
