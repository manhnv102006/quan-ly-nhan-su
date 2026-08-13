<x-admin-layout title="Chi tiết khiếu nại">
<div class="admin-page space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="mb-1 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">Chi tiết khiếu nại</h1>
                <x-view-only-badge />
            </div>
            <p class="text-slate-500">
                Mã {{ $payrollComplaint->complaint_code }} — Admin chỉ xem. Kế toán xử lý khiếu nại.
            </p>
        </div>
        <a href="{{ route('admin.payroll-complaints.index') }}" class="admin-btn-secondary">← Danh sách khiếu nại</a>
    </div>

    <div class="admin-card p-4 text-sm">
        <p class="font-semibold text-slate-800">{{ $payrollComplaint->employee?->full_name }} · {{ $payrollComplaint->employee?->employee_code }}</p>
        <p class="text-slate-500">{{ $payrollComplaint->employee?->department?->department_name }}</p>
    </div>

    @include('payroll-complaints.partials.detail', ['complaint' => $payrollComplaint])
</div>
</x-admin-layout>
