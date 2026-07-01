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

<x-admin-layout title="Nghỉ phép — {{ $selectedDepartment->department_name }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.leave-requests') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-800 transition mb-2">
                    ← Quay lại toàn công ty
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Phòng ban</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">{{ $selectedDepartment->department_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Đơn nghỉ phép · <span class="font-medium text-slate-600">{{ $selectedDepartment->department_code }}</span>
                </p>
            </div>
        </div>

        @include('admin.leave-requests.partials.list-body', [
            'filterRoute' => route('admin.leave-requests.department', $selectedDepartment),
            'clearFilterRoute' => route('admin.leave-requests.department', $selectedDepartment),
            'showDepartmentColumn' => false,
            'scopeLabel' => $selectedDepartment->department_name,
        ])
    </div>
</x-admin-layout>
