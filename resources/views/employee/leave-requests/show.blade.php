@php
    $user = Auth::user();
    $isAdmin = $user->role->name === 'admin';
    $isManager = $user->role->name === 'manager';

    $navigation = $isManager
        ? \App\Support\ManagerNavigation::items()
        : \App\Support\EmployeeNavigation::items();

    $layout = match (true) {
        $isAdmin => 'admin-layout',
        $isManager => 'manager-layout',
        default => 'employee-layout',
    };
    $layoutParams = $isAdmin
        ? ['title' => 'Chi tiết đơn nghỉ phép']
        : [
            'title' => 'Chi tiết đơn nghỉ phép',
            'subtitle' => 'Thông tin chi tiết đơn xin nghỉ phép của bạn.',
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
