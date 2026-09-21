@php
    $layout = \App\Support\SelfServiceLayout::component();
    $statusLabels = \App\Models\OvertimeRequest::STATUS_LABELS;
    $statusClasses = \App\Models\OvertimeRequest::STATUS_TAILWIND_CLASSES;
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag(['title' => 'Chi tiết đơn tăng ca', 'subtitle' => 'Mã đơn #'.$overtimeRequest->id])">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800">Chi tiết đơn tăng ca</h2>
            <a href="{{ route('employee.overtime-requests') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">Quay lại</a>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                <div><p class="text-slate-500">Ngày tăng ca</p><p class="font-semibold">{{ $overtimeRequest->work_date->format('d/m/Y') }}</p></div>
                <div><p class="text-slate-500">Khung giờ</p><p class="font-semibold">{{ \App\Support\TimeInput::forInput($overtimeRequest->start_time) }} → {{ \App\Support\TimeInput::forInput($overtimeRequest->end_time) }}</p></div>
                <div><p class="text-slate-500">Tổng giờ</p><p class="font-semibold">{{ number_format(abs((float) $overtimeRequest->total_hours), 1) }}h</p></div>
                <div><p class="text-slate-500">Trạng thái</p><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$overtimeRequest->status] ?? '' }}">{{ $statusLabels[$overtimeRequest->status] ?? $overtimeRequest->status }}</span></div>
                <div class="md:col-span-2"><p class="text-slate-500">Lý do / công việc</p><p class="font-medium text-slate-700">{{ $overtimeRequest->reason }}</p></div>
                <div class="md:col-span-2">
                    <p class="text-slate-500">Đồng thuận tự nguyện</p>
                    @if ($overtimeRequest->voluntary_consent_at)
                        <p class="font-medium text-emerald-700">Đã xác nhận lúc {{ $overtimeRequest->voluntary_consent_at->format('d/m/Y H:i') }}</p>
                    @else
                        <p class="font-medium text-slate-500">—</p>
                    @endif
                </div>
            </div>
        </div>

        @include('request-approvals.partials.processing-history', [
            'requestModel' => $overtimeRequest,
            'title' => 'Lịch sử xử lý',
        ])
    </div>
</x-dynamic-component>
