@php
    $navigation = [
        [
            'label' => 'Dashboard',
            'href' => route('manager.dashboard'),
            'route' => 'manager.dashboard',
            'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
            'note' => 'Tổng quan điều hành',
        ],
        [
            'label' => 'Đội ngũ',
            'href' => route('manager.employees.index'),
            'route' => 'manager.employees.*',
            'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
            'note' => 'Nhân sự & vai trò',
        ],
        [
            'label' => 'Phê duyệt',
            'href' => route('manager.dashboard') . '#approvals',
            'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'note' => 'Đơn nghỉ đang chờ',
        ],
        [
            'label' => 'KPI',
            'href' => route('manager.dashboard') . '#kpi',
            'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            'note' => 'Tiến độ phòng ban',
        ],
        [
            'label' => 'Nghỉ phép',
            'href' => route('manager.leave-requests'),
            'route' => 'manager.leave-requests*',
            'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z',
            'note' => 'Quản lý nghỉ phép',
        ],
        [
            'label' => 'Thông báo',
            'href' => route('manager.notifications.index'),
            'route' => 'manager.notifications*',
            'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
            'note' => 'Phòng ban của bạn',
        ],
        [
            'label' => 'Tuyển dụng',
            'href' => route('manager.dashboard') . '#recruitment',
            'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'note' => 'Tin tuyển đang mở',
        ],
        [
            'label' => 'Hồ sơ',
            'href' => route('profile.edit'),
            'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
            'note' => 'Thông tin tài khoản',
        ],
    ];
    $firstName = collect(explode(' ', trim(Auth::user()->name)))->filter()->first() ?? Auth::user()->name;
    $managerName = $employeeProfile?->full_name ?? Auth::user()->name;
    $departmentName = $department?->department_name ?? 'Chưa gắn phòng ban';
    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
        'resigned' => 'bg-rose-50 text-rose-700 border-rose-100',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
        'open' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
        'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $employeeStatusLabels = [
        'active' => 'Đang làm việc',
        'inactive' => 'Tạm ngưng',
        'resigned' => 'Đã nghỉ',
    ];
    $leaveTypeLabels = [
        'annual' => 'Nghỉ phép',
        'sick' => 'Nghỉ ốm',
        'unpaid' => 'Không lương',
    ];
    $leaveStatusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
    $jobStatusLabels = [
        'open' => 'Đang tuyển',
        'closed' => 'Đã đóng',
    ];
    $kpiCards = [
        ['label' => 'KPI chờ bắt đầu', 'value' => (int) ($kpiStatus->pending ?? 0), 'tone' => 'from-amber-400 to-orange-500'],
        ['label' => 'KPI đang chạy', 'value' => (int) ($kpiStatus->in_progress ?? 0), 'tone' => 'from-cyan-400 to-sky-500'],
        ['label' => 'KPI hoàn thành', 'value' => (int) ($kpiStatus->completed ?? 0), 'tone' => 'from-emerald-400 to-teal-500'],
    ];
@endphp

<x-staff-layout
    title="Bảng điều khiển quản lý"
    subtitle="Theo dõi đội ngũ, phê duyệt yêu cầu và nhịp độ vận hành của phòng ban."
    role="manager"
    :navigation="$navigation"
>
    <section id="overview" class="relative mb-8 overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-400 p-6 text-white shadow-xl shadow-emerald-400/15 sm:p-8">
        <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-40 w-40 -translate-x-1/4 translate-y-1/4 rounded-full bg-cyan-300/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-white"></span>
                    Điều phối đội nhóm theo thời gian thực
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                    Chào {{ $firstName }}, hôm nay mình kiểm soát nhịp vận hành của {{ $department?->department_code ? $departmentName.' ('.$department->department_code.')' : $departmentName }}.
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/90 sm:text-base">
                    Không gian manager được tối ưu để bạn nắm nhanh tiến độ đội ngũ, hồ sơ chờ phê duyệt và các hạng mục KPI cần theo dõi trong ngày.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="#approvals" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-emerald-700 shadow-lg shadow-emerald-900/10 transition hover:-translate-y-0.5">
                        Xem hàng đợi phê duyệt
                    </a>
                    <a href="#team" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        Mở danh sách đội ngũ
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:w-[420px] xl:grid-cols-1">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-100">Người phụ trách</p>
                    <p class="mt-2 text-lg font-bold">{{ $managerName }}</p>
                    <p class="mt-1 text-sm text-emerald-50/85">{{ $employeeProfile?->position_name ?? 'Manager' }}</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-100">Đơn đang chờ / Tổng đơn</p>
                    <p class="mt-2 text-lg font-bold">{{ number_format($pendingLeaves) }} / {{ number_format($totalLeaves) }}</p>
                    <p class="mt-1 text-sm text-emerald-50/85">Cần ưu tiên xử lý trong hôm nay</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-100">Thông báo mới</p>
                    <p class="mt-2 text-lg font-bold">{{ number_format($unreadNotifications) }}</p>
                    <p class="mt-1 text-sm text-emerald-50/85">Tin nhắn nội bộ chưa đọc</p>
                </div>
            </div>
        </div>
    </section>

    @if (! $employeeProfile)
        <div class="staff-card mb-8 border border-amber-100 bg-amber-50/90 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-amber-800">Tài khoản manager chưa liên kết hồ sơ nhân sự</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Dashboard vẫn hiển thị được giao diện, nhưng để lấy đúng phòng ban và dữ liệu đội nhóm bạn cần map tài khoản này với bảng `employees`.
                    </p>
                </div>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                    Cập nhật hồ sơ
                </a>
            </div>
        </div>
    @endif

    <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="staff-stat-card border border-emerald-100/80 bg-white/90">
            <div class="flex items-start justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-emerald-700">Team</span>
            </div>
            <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($teamCount) }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">Nhân sự trong phòng ban</p>
        </div>

        <div class="staff-stat-card border border-cyan-100/80 bg-white/90">
            <div class="flex items-start justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-lg shadow-cyan-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
                <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-700">Active</span>
            </div>
            <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($activeCount) }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">Nhân sự đang hoạt động</p>
        </div>

        <div class="staff-stat-card border border-amber-100/80 bg-white/90">
            <div class="flex items-start justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z" />
                    </svg>
                </div>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Leave</span>
            </div>
            <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($pendingLeaves) }} / {{ number_format($totalLeaves) }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">Đơn chờ duyệt / Tổng đơn</p>
        </div>

        <div class="staff-stat-card border border-sky-100/80 bg-white/90">
            <div class="flex items-start justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-lg shadow-sky-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-sky-700">KPI</span>
            </div>
            <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($kpiInProgress) }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">KPI đang theo dõi</p>
        </div>

        <div class="staff-stat-card border border-indigo-100/80 bg-white/90">
            <div class="flex items-start justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-600 text-white shadow-lg shadow-indigo-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-indigo-700">Today</span>
            </div>
            <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($todayCheckIns) }}</p>
            <p class="mt-1 text-sm font-medium text-slate-500">Nhân sự đã check-in hôm nay</p>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="team" class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Đội ngũ phòng ban</p>
                            <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">{{ $departmentName }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $department?->description ?: 'Khu vực này giúp manager theo dõi thành viên mới nhất và trạng thái làm việc của cả đội.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                {{ number_format($teamCount) }} thành viên
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$department?->status ?? 'inactive'] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                {{ $department?->status === 'active' ? 'Phòng ban hoạt động' : 'Chưa sẵn sàng' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 border-b border-slate-100 px-6 py-5 sm:grid-cols-3 sm:px-7">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Manager</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ $managerName }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->position_name ?? 'Quản lý phòng ban' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Thông báo chưa đọc</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ number_format($unreadNotifications) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Cập nhật nội bộ và nhắc việc</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tin tuyển mở</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ number_format($openJobs) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Nhu cầu tuyển dụng hiện tại</p>
                    </div>
                </div>

                <div class="px-6 py-5 sm:px-7">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-bold text-slate-800">Thành viên cập nhật gần đây</h4>
                            <p class="text-sm text-slate-500">Danh sách nhân sự mới hoặc vừa thay đổi trạng thái trong đội.</p>
                        </div>
                        <a href="{{ route('manager.employees.index') }}" class="inline-flex items-center rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                            Xem tất cả
                        </a>
                    </div>

                    @if ($teamMembers->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Chưa có thành viên nào được gắn vào phòng ban này.</p>
                            <p class="mt-1 text-sm text-slate-500">Khi dữ liệu nhân sự được cập nhật, danh sách đội ngũ sẽ xuất hiện ở đây.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($teamMembers as $member)
                                <div class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white px-4 py-4 shadow-sm shadow-slate-100 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-cyan-100 text-sm font-bold text-emerald-700">
                                            {{ strtoupper(mb_substr($member->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $member->full_name }}</p>
                                            <p class="text-sm text-slate-500">
                                                {{ $member->position_name ?? 'Chưa có chức vụ' }} · {{ $member->employee_code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$member->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                            {{ $employeeStatusLabels[$member->status] ?? ucfirst($member->status) }}
                                        </span>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            {{ \Illuminate\Support\Carbon::parse($member->hire_date)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section id="approvals" class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-600">Hàng đợi phê duyệt</p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Các yêu cầu cần xử lý</h3>
                    <p class="mt-1 text-sm text-slate-500">Ưu tiên duyệt nhanh các đơn nghỉ để đội ngũ không bị gián đoạn lịch làm việc.</p>
                </div>

                <div class="px-6 py-5 sm:px-7">
                    @if ($approvalQueue->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Hiện không có đơn nghỉ nào cần manager xử lý.</p>
                            <p class="mt-1 text-sm text-slate-500">Khi phát sinh yêu cầu mới, hàng đợi phê duyệt sẽ hiển thị tại đây.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($approvalQueue as $request)
                                <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $request->full_name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $leaveTypeLabels[$request->leave_type] ?? ucfirst($request->leave_type) }}
                                                · {{ \Illuminate\Support\Carbon::parse($request->start_date)->format('d/m/Y') }}
                                                đến {{ \Illuminate\Support\Carbon::parse($request->end_date)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$request->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                                {{ $leaveStatusLabels[$request->status] ?? ucfirst($request->status) }}
                                            </span>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm shadow-slate-100">
                                                {{ \Illuminate\Support\Carbon::parse($request->created_at)->format('d/m H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section id="kpi" class="staff-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Nhịp KPI</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-800">Sức khỏe mục tiêu</h3>
                        <p class="mt-1 text-sm text-slate-500">Tỷ lệ hoàn thành giúp bạn nhìn nhanh nhịp độ đội nhóm.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trung bình</p>
                        <p class="text-lg font-bold text-slate-800">{{ round($kpiStatus->average_progress ?? 0) }}%</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($kpiCards as $card)
                        <div class="rounded-3xl border border-slate-100 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-700">{{ $card['label'] }}</p>
                                <span class="text-lg font-bold text-slate-800">{{ number_format($card['value']) }}</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100">
                                <div
                                    class="h-2 rounded-full bg-gradient-to-r {{ $card['tone'] }}"
                                    style="width: {{ min(100, max(12, $teamCount > 0 ? round(($card['value'] / max($teamCount, 1)) * 100) : 12)) }}%;"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="staff-card p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Hồ sơ quản lý</p>
                <h3 class="mt-2 text-xl font-bold text-slate-800">Tóm tắt tài khoản</h3>

                <div class="mt-6 space-y-4">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Họ tên</p>
                        <p class="mt-2 text-base font-bold text-slate-800">{{ $managerName }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Phòng ban</p>
                        <p class="mt-2 text-base font-bold text-slate-800">{{ $departmentName }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->position_name ?? 'Manager' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Liên hệ</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">{{ $employeeProfile?->email ?? Auth::user()->email }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->phone ?? 'Chưa cập nhật số điện thoại' }}</p>
                    </div>
                </div>
            </section>

            <section id="recruitment" class="staff-card p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-cyan-600">Tuyển dụng</p>
                <h3 class="mt-2 text-xl font-bold text-slate-800">Tin tuyển của phòng ban</h3>
                <p class="mt-1 text-sm text-slate-500">Theo dõi những vị trí đang mở và nhu cầu bổ sung nhân sự.</p>

                @if ($recruitmentPosts->isEmpty())
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                        <p class="text-sm font-semibold text-slate-700">Chưa có tin tuyển nào trong phòng ban.</p>
                        <p class="mt-1 text-sm text-slate-500">Khi có nhu cầu tuyển thêm người, danh sách sẽ xuất hiện tại đây.</p>
                    </div>
                @else
                    <div class="mt-6 space-y-3">
                        @foreach ($recruitmentPosts as $post)
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $post->title }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Nhu cầu: {{ number_format($post->quantity) }} người</p>
                                    </div>
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$post->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $jobStatusLabels[$post->status] ?? ucfirst($post->status) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-slate-400">Cập nhật {{ \Illuminate\Support\Carbon::parse($post->created_at)->format('d/m/Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-staff-layout>
