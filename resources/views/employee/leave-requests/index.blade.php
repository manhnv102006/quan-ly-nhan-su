@php
    $user = Auth::user();
    $isAdmin = $user->role->name === 'admin';
    $isManager = $user->role->name === 'manager';

    $navigation = [];
    if ($isManager) {
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
                'href' => route('manager.leave-requests.index'),
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
    } else {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Chấm công', 'href' => route('employee.dashboard') . '#attendance', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lịch sử gần đây'],
            ['label' => 'KPI', 'href' => route('employee.dashboard') . '#kpi', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'note' => 'Mục tiêu công việc'],
            ['label' => 'Bảng lương', 'href' => route('employee.dashboard') . '#payroll', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Tổng thu nhập'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Thông báo', 'href' => route('employee.dashboard') . '#notices', 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'note' => 'Tin nội bộ'],
            ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
        ];
    }

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
    $layoutParams = $isAdmin
        ? ['title' => 'Đơn nghỉ phép của tôi']
        : [
            'title' => 'Đơn nghỉ phép của tôi',
            'subtitle' => 'Xem danh sách và gửi yêu cầu nghỉ phép.',
            'role' => $isManager ? 'manager' : 'employee',
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
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        @if (session('success'))
            <div id="success-toast" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Lịch sử nghỉ phép của bạn</h2>
                <p class="text-xs text-slate-500 mt-1">Danh sách đơn đã gửi và trạng thái xử lý.</p>
            </div>
            <a href="{{ route('employee.leave-requests.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 text-white font-semibold text-xs shadow-md shadow-sky-500/20 hover:bg-sky-700 transition">
                <span>➕</span> Tạo đơn nghỉ phép
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Loại nghỉ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian nghỉ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Số ngày</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Người duyệt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leaveRequests as $request)
                            <tr class="hover:bg-slate-50/50 transition">
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
                                <td class="px-6 py-4 text-slate-500 text-xs max-w-[200px] truncate" title="{{ $request->reason }}">
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
                                            <div class="mt-1 bg-red-50 text-red-700 border border-red-100 rounded-lg p-1.5 text-[10px]" title="Lý do từ chối: {{ $request->reject_reason }}">
                                                <strong>Lý do từ chối:</strong> {{ $request->reject_reason }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                                    Bạn chưa gửi đơn xin nghỉ phép nào.
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

    <script>
        const toast = document.getElementById('success-toast');
        if (toast) {
            setTimeout(function () {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-dynamic-component>
