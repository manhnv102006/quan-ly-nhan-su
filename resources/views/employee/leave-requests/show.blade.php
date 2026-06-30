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
                'label' => 'Nghỉ phép',
                'href' => route('manager.leave-requests.index'),
                'route' => 'manager.leave-requests*',
                'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z',
                'note' => 'Quản lý nghỉ phép',
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
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
        ];
    }

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
    $layoutParams = $isAdmin
        ? ['title' => 'Chi tiết đơn nghỉ phép']
        : [
            'title' => 'Chi tiết đơn nghỉ phép',
            'subtitle' => 'Thông tin chi tiết đơn xin nghỉ phép của bạn.',
            'role' => $isManager ? 'manager' : 'employee',
            'navigation' => $navigation,
        ];

    $leaveTypes = \App\Models\LeaveRequest::LEAVE_TYPE_LABELS;
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Chi tiết đơn nghỉ phép</h2>
                <p class="text-xs text-slate-500 mt-1">Mã đơn #{{ $leaveRequest->id }}</p>
            </div>
            <a href="{{ route('employee.leave-requests') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold text-xs hover:bg-slate-200 transition">
                Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase text-slate-400 mb-5">Thông tin nghỉ phép</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <p class="text-sm text-slate-500">Loại nghỉ</p>
                    <p class="font-semibold">{{ $leaveTypes[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Số ngày</p>
                    <p class="font-semibold">{{ $leaveRequest->total_days }} ngày</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Từ ngày</p>
                    <p class="font-semibold">{{ $leaveRequest->start_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Đến ngày</p>
                    <p class="font-semibold">{{ $leaveRequest->end_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Trạng thái</p>
                    <p><x-status-badge :model="$leaveRequest" /></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase text-slate-400 mb-5">Lý do nghỉ phép</h3>
            <p class="text-slate-700">{{ $leaveRequest->reason }}</p>
        </div>

        @if(in_array($leaveRequest->status, [\App\Models\LeaveRequest::STATUS_APPROVED, \App\Models\LeaveRequest::STATUS_REJECTED], true))
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-bold uppercase text-slate-400 mb-5">Thông tin xử lý</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @include('leave-requests.partials.approval-info', [
                        'leaveRequest' => $leaveRequest,
                        'variant' => 'tailwind',
                    ])
                </div>
            </div>
        @endif

        @if($leaveRequest->histories->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-bold uppercase text-slate-400 mb-5">Lịch sử xử lý</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-xs uppercase text-slate-400">
                                <th class="pb-3 pr-4">Người xử lý</th>
                                <th class="pb-3 pr-4">Hành động</th>
                                <th class="pb-3">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($leaveRequest->histories->sortByDesc('created_at') as $history)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-slate-700">{{ $history->actor?->name ?? '—' }}</td>
                                    <td class="py-3 pr-4"><x-approval-action-badge :action="$history->action" /></td>
                                    <td class="py-3 text-slate-500">{{ optional($history->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

</x-dynamic-component>
