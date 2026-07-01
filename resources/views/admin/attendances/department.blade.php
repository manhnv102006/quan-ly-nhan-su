<x-admin-layout title="Chấm công — {{ $selectedDepartment->department_name }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.attendances') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-800 transition mb-2">
                    ← Quay lại danh sách phòng ban
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Phòng ban</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">{{ $selectedDepartment->department_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Chấm công · <span class="font-medium text-slate-600">{{ $selectedDepartment->department_code }}</span>
                </p>
            </div>
        </div>

        @include('admin.attendances.partials.list-body', [
            'filterRoute' => route('admin.attendances.department', $selectedDepartment),
            'clearFilterRoute' => route('admin.attendances.department', $selectedDepartment),
            'showDepartmentColumn' => false,
            'scopeLabel' => $selectedDepartment->department_name,
        ])
    </div>
</x-admin-layout>
