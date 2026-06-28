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
            'note' => 'Nhân viên phòng ban',
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

    $statusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
        'resigned' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $statusLabels = [
        'active' => 'Đang làm việc',
        'inactive' => 'Tạm ngưng',
        'resigned' => 'Đã nghỉ',
    ];
@endphp

<x-staff-layout title="Nhân viên phòng ban" subtitle="Xem danh sách nhân viên thuộc phòng ban bạn quản lý." role="manager" :navigation="$navigation">
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 p-6 text-white shadow-xl shadow-emerald-500/20 sm:p-8">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">Manager Team</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight">Nhân viên thuộc phòng ban mình</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50/90">
                        {{ $department ? 'Phòng ban: '.$department->department_name : 'Tài khoản manager chưa được gắn với phòng ban nào.' }}
                    </p>
                </div>
                <a href="{{ route('manager.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-emerald-700 shadow-lg shadow-emerald-900/10 hover:bg-emerald-50">
                    ← Dashboard
                </a>
            </div>
        </section>

        @if (! $department)
            <div class="staff-card border border-amber-100 bg-amber-50/90 p-6">
                <h3 class="text-base font-bold text-amber-800">Chưa xác định được phòng ban quản lý</h3>
                <p class="mt-1 text-sm text-amber-700">Vui lòng liên hệ admin để gắn tài khoản manager với hồ sơ nhân viên hoặc phân công làm quản lý phòng ban.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="staff-stat-card border border-slate-100 bg-white/90">
                    <p class="text-sm font-medium text-slate-500">Tổng nhân viên</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-800">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="staff-stat-card border border-emerald-100 bg-white/90">
                    <p class="text-sm font-medium text-slate-500">Đang làm việc</p>
                    <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="staff-stat-card border border-slate-100 bg-white/90">
                    <p class="text-sm font-medium text-slate-500">Tạm ngưng</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-600">{{ number_format($stats['inactive']) }}</p>
                </div>
                <div class="staff-stat-card border border-rose-100 bg-white/90">
                    <p class="text-sm font-medium text-slate-500">Đã nghỉ</p>
                    <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($stats['resigned']) }}</p>
                </div>
            </div>

            <div class="staff-card p-5">
                <form method="GET" action="{{ route('manager.employees.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Tìm kiếm</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Tên, mã NV, email hoặc số điện thoại..."
                               class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Trạng thái</label>
                        <div class="flex gap-2">
                            <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                                <option value="">Tất cả</option>
                                <option value="active" @selected($status === 'active')>Đang làm việc</option>
                                <option value="inactive" @selected($status === 'inactive')>Tạm ngưng</option>
                                <option value="resigned" @selected($status === 'resigned')>Đã nghỉ</option>
                            </select>
                            <button class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">Lọc</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="staff-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-xl font-bold text-slate-800">Danh sách nhân viên</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $employees->total() }} nhân viên thuộc {{ $department->department_name }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                                <th class="px-6 py-4">Nhân viên</th>
                                <th class="px-6 py-4">Chức vụ</th>
                                <th class="px-6 py-4">Liên hệ</th>
                                <th class="px-6 py-4">Ngày vào làm</th>
                                <th class="px-6 py-4">Tài khoản</th>
                                <th class="px-6 py-4 text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-100 to-cyan-100 font-bold text-emerald-700">
                                                {{ strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">{{ $employee->full_name }}</p>
                                                <p class="text-sm text-slate-500">{{ $employee->employee_code }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $employee->position?->position_name ?? 'Chưa có chức vụ' }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-700">{{ $employee->email }}</p>
                                        <p class="text-sm text-slate-500">{{ $employee->phone }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        @if ($employee->user)
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Đã liên kết</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Chưa liên kết</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$employee->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                            {{ $statusLabels[$employee->status] ?? ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">Không có nhân viên nào phù hợp.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-staff-layout>
