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
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý nghỉ phép</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">Quản lý nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Theo dõi toàn bộ đơn nghỉ phép —
                    <span class="font-semibold text-slate-700">Toàn công ty</span>.
                    Admin phê duyệt đơn của <span class="font-semibold text-slate-700">quản lý</span>.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('employee.leave-requests') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                    Đơn cá nhân
                </a>
                <a href="{{ route('employee.leave-requests.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Tạo đơn mới
                </a>
            </div>
        </div>

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.leave-requests.department',
        ])

        @include('admin.leave-requests.partials.list-body', [
            'filterRoute' => route('admin.leave-requests'),
            'clearFilterRoute' => route('admin.leave-requests'),
            'showDepartmentColumn' => true,
            'scopeLabel' => 'Toàn công ty',
        ])
    </div>
</x-admin-layout>
