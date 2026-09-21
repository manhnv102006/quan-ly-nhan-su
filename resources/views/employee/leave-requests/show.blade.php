@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isAdmin = $roleName === 'admin';

    $navigation = \App\Support\SelfServiceLayout::navigation();
    $layout = \App\Support\SelfServiceLayout::component($roleName);
    $layoutParams = $isAdmin
        ? ['title' => 'Chi tiết đơn nghỉ phép']
        : [
            'title' => 'Chi tiết đơn nghỉ phép',
            'subtitle' => 'Thông tin chi tiết đơn xin nghỉ phép của bạn.',
        ];

    $leaveTypes = \App\Models\LeaveRequest::leaveTypeLabels();
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
                    <p class="font-semibold">{{ $leaveTypes[$leaveRequest->leave_type] ?? $leaveRequest->leaveTypeLabel() }}</p>
                    @include('shared.leave-type-policy', ['leaveRequest' => $leaveRequest])
                </div>
                <div>
                    <p class="text-sm text-slate-500">Số ngày</p>
                    <p class="font-semibold">
                        {{ $leaveRequest->total_days }} ngày
                        @if($leaveRequest->halfDayPeriodLabel())
                            <span class="text-slate-500">({{ $leaveRequest->halfDayPeriodLabel() }})</span>
                        @endif
                    </p>
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

        @if($leaveRequest->document)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-bold uppercase text-slate-400 mb-5">Giấy tờ minh chứng</h3>
                <a href="{{ route('employee.leave-requests.document', $leaveRequest) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-xs font-semibold text-sky-800 hover:bg-sky-100 transition">
                    Tải xuống: {{ $leaveRequest->document->original_name }}
                </a>
            </div>
        @endif

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
            @include('request-approvals.partials.processing-history', [
                'requestModel' => $leaveRequest,
                'title' => 'Lịch sử xử lý',
            ])
        @endif

    </div>

</x-dynamic-component>
