
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

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-slate-800">Danh sách đơn nghỉ phép</h2>
                    <x-view-only-badge />
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    Xem toàn bộ đơn nghỉ phép và thống kê. Admin không có quyền duyệt/từ chối.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('employee.leave-requests') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold text-xs shadow-sm hover:bg-slate-200 transition">
                    👤 Đơn cá nhân của tôi
                </a>
                <a href="{{ route('employee.leave-requests.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white font-semibold text-xs shadow-md shadow-violet-500/20 hover:bg-violet-700 transition">
                    ➕ Tạo đơn nghỉ phép
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
            <form action="{{ route('admin.leave-requests') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <a href="{{ route('admin.leave-requests') }}"
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
                                    @if ($request->status === 'approved' && $request->approver)
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $request->approver->name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">lúc {{ $request->approved_at?->format('H:i d/m/Y') }}</span>
                                        </div>
                                    @elseif ($request->status === 'rejected' && $request->rejecter)
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $request->rejecter->name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">lúc {{ $request->rejected_at?->format('H:i d/m/Y') }}</span>
                                        </div>
                                        @if ($request->reject_reason)
                                            <div class="mt-1 bg-red-50 text-red-700 border border-red-100 rounded-lg p-1.5 text-[10px] max-w-[200px] break-words" title="Lý do từ chối: {{ $request->reject_reason }}">
                                                <strong>Lý do:</strong> {{ $request->reject_reason }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-view-only-badge :href="route('admin.leave-requests.show', $request)" />
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

</x-admin-layout>

