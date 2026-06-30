@php
    $leaveTypes = [
        'annual' => ['label' => 'Nghỉ phép năm', 'class' => 'bg-sky-50 text-sky-700 border-sky-100'],
        'sick' => ['label' => 'Nghỉ ốm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'unpaid' => ['label' => 'Nghỉ không lương', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
    ];

    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];

    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-admin-layout title="Quản lý nghỉ phép">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="mb-1 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-800">Quản lý nghỉ phép</h2>
                    <x-view-only-badge />
                </div>
                <p class="text-sm text-slate-500">
                    Theo dõi toàn bộ đơn nghỉ phép. Admin chỉ xem, quyền duyệt thuộc quản lý phòng ban.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('employee.leave-requests') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                    Đơn cá nhân
                </a>
                <a href="{{ route('employee.leave-requests.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Tạo đơn mới
                </a>
            </div>
        </div>

        {{-- Thống kê --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Tổng đơn', 'value' => $stats['total'], 'tone' => 'text-slate-800', 'badge' => 'All'],
                ['label' => 'Chờ duyệt', 'value' => $stats['pending'], 'tone' => 'text-amber-600', 'badge' => 'Pending'],
                ['label' => 'Đã duyệt', 'value' => $stats['approved'], 'tone' => 'text-emerald-600', 'badge' => 'OK'],
                ['label' => 'Từ chối', 'value' => $stats['rejected'], 'tone' => 'text-rose-600', 'badge' => 'No'],
            ] as $card)
                <div class="admin-stat-card border border-slate-100 bg-white/90">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-600">{{ $card['badge'] }}</span>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Bộ lọc --}}
        <div class="admin-card p-5 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Tìm kiếm &amp; lọc</h3>
                    <p class="text-xs text-slate-500">Tìm theo tên, mã nhân viên, trạng thái hoặc loại nghỉ</p>
                </div>
                @if($hasFilters)
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                        Đang lọc · {{ $leaveRequests->total() }} kết quả
                    </span>
                @endif
            </div>

            <form action="{{ route('admin.leave-requests') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label for="search" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Từ khóa</label>
                    <input type="text" id="search" name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Tên hoặc mã nhân viên (VD: EMP002, Nguyễn)"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>

                <div>
                    <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                    <select id="status" name="status"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        <option value="">Tất cả trạng thái</option>
                        @foreach ($statusLabels as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="leave_type" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Loại nghỉ</label>
                    <select id="leave_type" name="leave_type"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        <option value="">Tất cả loại nghỉ</option>
                        @foreach (\App\Models\LeaveRequest::LEAVE_TYPE_LABELS as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['leave_type'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-4">
                    <button type="submit" class="admin-btn-primary">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Tìm kiếm</span>
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.leave-requests') }}"
                           class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Xóa bộ lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bảng --}}
        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Danh sách đơn nghỉ phép</h3>
                <p class="text-xs text-slate-500">Hiển thị {{ $leaveRequests->count() }} / {{ $leaveRequests->total() }} đơn</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Loại nghỉ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Thời gian</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Số ngày</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Lý do</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Quản lý xử lý</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leaveRequests as $leaveRequest)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-800">{{ $leaveRequest->employee?->full_name ?: '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $leaveRequest->employee?->employee_code ?: '—' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $leaveTypes[$leaveRequest->leave_type]['class'] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $leaveTypes[$leaveRequest->leave_type]['label'] ?? $leaveRequest->leave_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs font-medium text-slate-700">
                                    {{ $leaveRequest->start_date->format('d/m/Y') }} → {{ $leaveRequest->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-4 text-center text-sm font-bold text-slate-800">
                                    {{ $leaveRequest->total_days }}
                                </td>
                                <td class="max-w-[180px] truncate px-5 py-4 text-xs text-slate-500" title="{{ $leaveRequest->reason }}">
                                    {{ $leaveRequest->reason }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$leaveRequest->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $statusLabels[$leaveRequest->status] ?? $leaveRequest->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    @if ($leaveRequest->status === 'approved')
                                        <p class="font-semibold text-slate-800">{{ $leaveRequest->approverDisplayName() ?? '—' }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-400">Duyệt lúc {{ $leaveRequest->approved_at?->format('H:i d/m/Y') }}</p>
                                    @elseif ($leaveRequest->status === 'rejected')
                                        <p class="font-semibold text-slate-800">{{ $leaveRequest->rejecterDisplayName() ?? '—' }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-400">Từ chối lúc {{ $leaveRequest->rejected_at?->format('H:i d/m/Y') }}</p>
                                        @if ($leaveRequest->reject_reason)
                                            <p class="mt-1 line-clamp-2 text-[11px] text-rose-600" title="{{ $leaveRequest->reject_reason }}">
                                                {{ $leaveRequest->reject_reason }}
                                            </p>
                                        @endif
                                    @else
                                        <span class="text-slate-400">Chưa xử lý</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <x-view-only-badge :href="route('admin.leave-requests.show', $leaveRequest)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-600">Không tìm thấy đơn nghỉ phép phù hợp.</p>
                                    @if($hasFilters)
                                        <a href="{{ route('admin.leave-requests') }}" class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                                            Xóa bộ lọc để xem tất cả
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaveRequests->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
