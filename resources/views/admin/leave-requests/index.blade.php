
@php
    $isAdmin = Auth::user()->role->name === 'admin';
    
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
            'href' => route('manager.dashboard') . '#team',
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

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
    $layoutParams = $isAdmin
        ? ['title' => 'Quản lý nghỉ phép']
        : [
            'title' => 'Quản lý nghỉ phép',
            'subtitle' => 'Xem danh sách và bộ lọc lịch sử nghỉ phép toàn công ty.',
            'role' => 'manager',
            'navigation' => $navigation
        ];

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

    $filterRoute = $isAdmin ? route('admin.leave-requests.index') : route('manager.leave-requests');
    $approveRouteName = $isAdmin ? 'admin.leave-requests.approve' : 'manager.leave-requests.approve';
    $rejectRouteName = $isAdmin ? 'admin.leave-requests.reject' : 'manager.leave-requests.reject';
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách đơn nghỉ phép</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Xem, tìm kiếm và phê duyệt/từ chối yêu cầu nghỉ phép của nhân sự.
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
    </div>

    <!-- Thông báo Success -->
    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Thông báo Error -->
    @if (session('error'))
        <div id="error-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
        </div>
    @endif

    <script>
        function openRejectModal(actionUrl, employeeName) {
            const modal = document.getElementById('reject-modal');
            const form = document.getElementById('reject-form');
            const nameSpan = document.getElementById('reject-employee-name');
            
            form.action = actionUrl;
            nameSpan.textContent = employeeName;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeRejectModal() {
            const modal = document.getElementById('reject-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('reject_reason').value = '';
        }

        document.getElementById('reject-modal').addEventListener('click', function (e) {
            if (e.target === this) closeRejectModal();
        });

        // Tự tắt Toast thông báo sau 4 giây
        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }

        const errorToast = document.getElementById('error-toast');
        if (errorToast) {
            setTimeout(function () {
                errorToast.style.transition = 'opacity 0.3s ease';
                errorToast.style.opacity = '0';
                setTimeout(function () { errorToast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-dynamic-component>

<x-admin-layout>

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold">
                Quản lý nghỉ phép
            </h1>

            <p class="text-slate-500">
                Duyệt và quản lý đơn nghỉ phép
            </p>
        </div>

        <div class="grid grid-cols-4 gap-4">

            <div class="bg-white p-5 rounded-xl border">
                <p>Tổng đơn</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['total'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Chờ duyệt</p>
                <h3 class="text-3xl font-bold text-yellow-500">
                    {{ $stats['pending'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Đã duyệt</p>
                <h3 class="text-3xl font-bold text-green-600">
                    {{ $stats['approved'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Từ chối</p>
                <h3 class="text-3xl font-bold text-red-600">
                    {{ $stats['rejected'] }}
                </h3>
            </div>

        </div>

        <div class="bg-white rounded-xl border overflow-hidden">

            <table class="w-full">

                <thead class="bg-slate-100">

                    <tr>
                        <th class="p-3 text-left">Nhân viên</th>
                        <th class="p-3 text-left">Phòng ban</th>
                        <th class="p-3 text-left">Loại nghỉ</th>
                        <th class="p-3 text-left">Từ ngày</th>
                        <th class="p-3 text-left">Đến ngày</th>
                        <th class="p-3 text-left">Trạng thái</th>
                        <th class="p-3 text-center">Thao tác</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($leaveRequests as $request)

                    <tr class="border-t">

                        <td class="p-3">
                            {{ $request->employee?->full_name }}
                        </td>

                        <td class="p-3">
                            {{ $request->employee?->department?->department_name }}
                        </td>

                        <td class="p-3">
                            {{ $request->leave_type }}
                        </td>

                        <td class="p-3">
                            {{ $request->start_date->format('d/m/Y') }}
                        </td>

                        <td class="p-3">
                            {{ $request->end_date->format('d/m/Y') }}
                        </td>

                        <td class="p-3">

                            @if($request->status == 'pending')
                                <span class="badge bg-warning">
                                    Chờ duyệt
                                </span>
                            @elseif($request->status == 'approved')
                                <span class="badge bg-success">
                                    Đã duyệt
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Từ chối
                                </span>
                            @endif

                        </td>

                        <td class="p-3 text-center">

                            <a
                                href="{{ route('admin.leave-requests.show',$request) }}"
                                class="btn btn-primary btn-sm">

                                Chi tiết

                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        {{ $leaveRequests->links() }}

    </div>

</x-admin-layout>

