@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = ['title' => 'Chi tiết khiếu nại', 'subtitle' => $payrollComplaint->complaint_code];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="space-y-6">
        <a href="{{ route('employee.payroll-complaints.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">← Danh sách khiếu nại</a>
        @include('payroll-complaints.partials.detail', ['complaint' => $payrollComplaint])
    </div>
</x-dynamic-component>
