@php
    $navigation = \App\Support\ManagerNavigation::items();
    $statusClasses = \App\Models\OvertimeRequest::STATUS_TAILWIND_CLASSES;
    $statusLabels = \App\Models\OvertimeRequest::STATUS_LABELS;
@endphp

<x-staff-layout
    title="Duyệt tăng ca"
    subtitle="Thống kê và danh sách chỉ tính trên nhân viên thuộc quyền quản lý."
    role="manager"
    :navigation="$navigation"
>
    <div class="space-y-6">
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
                            nên không thể tải danh sách đơn tăng ca cần duyệt.
                        </p>
                        <p class="mt-2 text-xs text-amber-600/90">
                            Vui lòng liên hệ quản trị viên để gán <code class="rounded bg-amber-100 px-1">user_id</code>
                            trên bảng nhân viên. Sau khi liên kết, hệ thống sẽ lấy đơn cấp dưới theo
                            <code class="rounded bg-amber-100 px-1">manager_id</code> hoặc phòng ban được giao quản lý.
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        @else
            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="staff-stat-card border border-amber-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Chờ</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['pending']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đơn chờ duyệt</p>
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
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Tìm kiếm đơn tăng ca</h3>
                </div>
                <div class="px-6 py-5 sm:px-7">
                    <form method="GET" action="{{ route('manager.overtime-requests.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Trạng thái</label>
                            <select name="status" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                                <option value="">Tất cả</option>
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tăng ca từ ngày</label>
                            <input type="date" name="work_date_from" value="{{ $filters['work_date_from'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tăng ca đến ngày</label>
                            <input type="date" name="work_date_to" value="{{ $filters['work_date_to'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                        </div>
                        <div class="flex flex-wrap items-end justify-end gap-2 md:col-span-2 xl:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-900/20 transition hover:bg-violet-700">
                                Tìm kiếm
                            </button>
                            @if(collect($filters)->filter()->isNotEmpty())
                                <a href="{{ route('manager.overtime-requests.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
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
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Đơn tăng ca cấp dưới</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">#</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Ngày tăng ca</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Khung giờ</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Tổng giờ</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($overtimeRequests as $index => $item)
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ ($overtimeRequests->firstItem() ?? 0) + $index }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ optional($item->work_date)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        {{ $item->start_time }} → {{ $item->end_time }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">{{ $item->total_hours }}h</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('manager.overtime-requests.show', $item) }}"
                                           class="inline-flex items-center rounded-lg border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Không có đơn tăng ca phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($overtimeRequests->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $overtimeRequests->links() }}
                    </div>
                @endif
            </section>

            @include('manager.overtime-requests.partials.history-table', [
                'histories' => $recentHistories,
                'showEmployee' => true,
                'showOvertimeRequestLink' => true,
            ])
        @endif
    </div>
</x-staff-layout>
