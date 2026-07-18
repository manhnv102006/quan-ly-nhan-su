@php
    $statusClasses = [
        'active' => 'bg-blue-50 text-blue-700 border-blue-100',
        'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
        'resigned' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $statusLabels = [
        'active' => 'Đang làm việc',
        'inactive' => 'Tạm ngưng',
        'resigned' => 'Đã nghỉ',
    ];
@endphp

<x-manager-layout title="Nhân viên phòng ban" subtitle="Xem danh sách nhân viên thuộc phòng ban bạn quản lý.">
    <div class="manager-page">
        <section class="manager-hero">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">Manager Team</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight">Nhân viên thuộc phòng ban mình</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-100/90">
                        {{ $department ? 'Phòng ban: '.$department->department_name : 'Tài khoản manager chưa được gắn với phòng ban nào.' }}
                    </p>
                </div>
                <a href="{{ route('manager.dashboard') }}" class="manager-btn-primary bg-white text-teal-700 shadow-lg hover:bg-teal-50">
                    ← Dashboard
                </a>
            </div>
        </section>

        @if (! $department)
            <div class="manager-card border border-amber-100 bg-amber-50/90 p-6">
                <h3 class="text-base font-bold text-amber-800">Chưa xác định được phòng ban quản lý</h3>
                <p class="mt-1 text-sm text-amber-700">Vui lòng liên hệ admin để gắn tài khoản manager với hồ sơ nhân viên hoặc phân công làm quản lý phòng ban.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="manager-stat-card">
                    <p class="text-sm font-medium text-slate-500">Tổng nhân viên</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-800">{{ number_format($stats['total']) }}</p>
                </div>
            <div class="manager-stat-card border border-teal-100">
                    <p class="text-sm font-medium text-slate-500">Đang làm việc</p>
                    <p class="mt-3 text-3xl font-extrabold text-teal-600">{{ number_format($stats['active']) }}</p>
                </div>
            <div class="manager-stat-card">
                    <p class="text-sm font-medium text-slate-500">Tạm ngưng</p>
                    <p class="mt-3 text-3xl font-extrabold text-slate-600">{{ number_format($stats['inactive']) }}</p>
                </div>
            <div class="manager-stat-card border border-rose-100">
                    <p class="text-sm font-medium text-slate-500">Đã nghỉ</p>
                    <p class="mt-3 text-3xl font-extrabold text-rose-600">{{ number_format($stats['resigned']) }}</p>
                </div>
            </div>

            <div class="manager-card p-5">
                <form method="GET" action="{{ route('manager.employees.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="manager-label">Tìm kiếm</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Tên, mã NV, email hoặc số điện thoại..." class="manager-field">
                    </div>
                    <div>
                        <label class="manager-label">Trạng thái</label>
                        <div class="flex gap-2">
                            <select name="status" class="manager-field">
                                <option value="">Tất cả</option>
                                <option value="active" @selected($status === 'active')>Đang làm việc</option>
                                <option value="inactive" @selected($status === 'inactive')>Tạm ngưng</option>
                                <option value="resigned" @selected($status === 'resigned')>Đã nghỉ</option>
                            </select>
                            <button type="submit" class="manager-btn-primary shrink-0">Lọc</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="manager-panel">
                <div class="manager-panel-header">
                    <h3 class="text-xl font-bold text-slate-800">Danh sách nhân viên</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $employees->total() }} nhân viên thuộc {{ $department->department_name }}</p>
                </div>

                <div class="manager-table-wrap overflow-x-auto">
                    <table class="manager-table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Chức vụ</th>
                                <th>Liên hệ</th>
                                <th>Ngày vào làm</th>
                                <th>Tài khoản</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-100 to-emerald-100 font-bold text-teal-700">
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
                                            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">Đã liên kết</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Chưa liên kết</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$employee->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                            {{ $statusLabels[$employee->status] ?? ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('manager.employees.show', $employee) }}"
                                           class="inline-flex items-center rounded-xl bg-teal-50 px-3 py-2 text-xs font-semibold text-teal-700 hover:bg-teal-100">
                                            Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">Không có nhân viên nào phù hợp.</td>
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
</x-manager-layout>
