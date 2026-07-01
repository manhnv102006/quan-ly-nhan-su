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
@endphp

<x-admin-layout title="Quản lý nghỉ phép">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý nghỉ phép</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">Quản lý nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Theo dõi toàn bộ đơn nghỉ phép —
                    <span class="font-semibold text-slate-700">Toàn công ty</span>
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


        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng đơn nghỉ phép</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-800">
                    {{ number_format($stats['total']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đang chờ duyệt</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">
                    {{ number_format($stats['pending']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã phê duyệt</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format($stats['approved']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã từ chối</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">
                    {{ number_format($stats['rejected']) }}
                </h3>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ $filterRoute }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-xs font-bold text-slate-500 uppercase mb-2">Tìm kiếm nhân viên</label>
                    <div class="relative">
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Nhập tên nhân viên..."
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <span class="absolute left-3 top-3.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-slate-500 uppercase mb-2">Lọc theo trạng thái</label>
                    <select id="status" name="status"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        @foreach ($statusLabels as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 bg-violet-600 text-white font-medium px-5 py-3 rounded-xl hover:bg-violet-700 transition shadow-lg shadow-violet-500/10 text-sm">
                        Lọc kết quả
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ $filterRoute }}"
                           class="bg-slate-100 text-slate-700 font-medium px-5 py-3 rounded-xl hover:bg-slate-200 transition text-sm text-center">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bảng danh sách -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Loại nghỉ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thời gian nghỉ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Số ngày</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Chi tiết phê duyệt</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leaveRequests as $request)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $request->employee?->employee_code ?: '—' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $request->employee?->full_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-semibold {{ $leaveTypes[$request->leave_type]['class'] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $leaveTypes[$request->leave_type]['label'] ?? $request->leave_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700 text-xs font-medium">
                                    {{ $request->start_date->format('d/m/Y') }} → {{ $request->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-center text-slate-800 font-bold text-xs">
                                    {{ $request->total_days }} ngày
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs max-w-[160px] truncate" title="{{ $request->reason }}">
                                    {{ $request->reason }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClasses[$request->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$request->status] ?? $request->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">
                                    @if ($request->status !== 'pending' && $request->approver)
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $request->approver->name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">lúc {{ $request->approved_at?->format('H:i d/m/Y') }}</span>
                                        </div>
                                        @if ($request->status === 'rejected' && $request->reject_reason)
                                            <div class="mt-1 bg-red-50 text-red-700 border border-red-100 rounded-lg p-1.5 text-[10px] max-w-[200px] break-words" title="Lý do từ chối: {{ $request->reject_reason }}">
                                                <strong>Lý do:</strong> {{ $request->reject_reason }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        @if ($request->status === 'pending')
                                            <form action="{{ route($approveRouteName, $request) }}" method="POST"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt đơn nghỉ phép của nhân viên {{ $request->employee?->full_name }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm">
                                                    Duyệt
                                                </button>
                                            </form>
                                            <button type="button"
                                                    onclick="openRejectModal('{{ route($rejectRouteName, $request) }}', '{{ $request->employee?->full_name }}')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition shadow-sm">
                                                Từ chối
                                            </button>
                                        @else
                                            <span class="text-slate-400 text-xs">Đã xử lý</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400 text-sm">
                                    Không tìm thấy dữ liệu đơn nghỉ phép phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaveRequests->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Từ Chối -->
    <div id="reject-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Từ chối đơn nghỉ phép</h3>
            <p class="text-sm text-slate-500 mb-4">
                Nhập lý do từ chối cho nhân viên <strong id="reject-employee-name" class="text-slate-800"></strong>:
            </p>
            <form id="reject-form" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-5">
                    <label for="reject_reason" class="block text-sm font-semibold text-slate-700 mb-2">Lý do từ chối</label>
                    <textarea id="reject_reason" name="reject_reason" required rows="3"
                              placeholder="Nhập lý do từ chối..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()"
                            class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition text-sm">
                        Hủy
                    </button>
                    <button type="submit"
                            class="flex-1 px-5 py-3 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 transition text-sm">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.leave-requests.department',
        ])

        @include('admin.leave-requests.partials.list-body', [
            'filterRoute' => route('admin.leave-requests'),
            'clearFilterRoute' => route('admin.leave-requests'),
            'showDepartmentColumn' => true,
            'scopeLabel' => 'Toàn công ty',
        ])

    </div>
</x-admin-layout>
