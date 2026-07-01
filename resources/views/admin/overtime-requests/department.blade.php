<x-admin-layout title="Duyệt tăng ca — {{ $selectedDepartment->department_name }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.overtime-requests.index') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-800 transition mb-2">
                    ← Quay lại toàn công ty
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Phòng ban</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">{{ $selectedDepartment->department_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Đơn tăng ca · <span class="font-medium text-slate-600">{{ $selectedDepartment->department_code }}</span>
                </p>
            </div>
            <a href="{{ route('admin.overtime-requests.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-900/20 transition hover:bg-violet-700">
                <i class="bi bi-plus-lg"></i>
                Tạo đơn
            </a>
        </div>

        <x-flash-messages />

        @include('admin.overtime-requests.partials.list-body', [
            'showDepartmentColumn' => false,
            'scopeLabel' => $selectedDepartment->department_name,
        ])
    </div>
</x-admin-layout>
