@php
    $user = Auth::user();
    $layout = $user->role->name === 'manager' ? 'manager-layout' : 'employee-layout';
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag(['title' => 'Chi tiết đơn về sớm', 'subtitle' => 'Mã đơn #'.$earlyLeaveRequest->id])">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Chi tiết đơn xin về sớm</h2>
            <a href="{{ route('employee.early-leave.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Quay lại</a>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                <div><p class="text-slate-500">Ngày</p><p class="font-semibold">{{ $earlyLeaveRequest->request_date->format('d/m/Y') }}</p></div>
                <div><p class="text-slate-500">Giờ muốn về</p><p class="font-semibold text-violet-600">{{ \Carbon\Carbon::parse($earlyLeaveRequest->leave_time)->format('H:i') }}</p></div>
                <div><p class="text-slate-500">Trạng thái</p><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $earlyLeaveRequest->statusBadgeClass() }}">{{ $earlyLeaveRequest->statusLabel() }}</span></div>
                <div class="md:col-span-2"><p class="text-slate-500">Lý do</p><p class="font-medium text-slate-700">{{ $earlyLeaveRequest->reason }}</p></div>
            </div>
        </div>

        @include('request-approvals.partials.processing-history', [
            'requestModel' => $earlyLeaveRequest,
            'title' => 'Lịch sử xử lý',
        ])
    </div>
</x-dynamic-component>
