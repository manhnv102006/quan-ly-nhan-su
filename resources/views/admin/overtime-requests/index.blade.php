<x-admin-layout title="Danh sách đơn tăng ca">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">Duyệt tăng ca</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Theo dõi và duyệt đơn tăng ca —
                    <span class="font-semibold text-slate-700">Toàn công ty</span>
                </p>
            </div>
            <a href="{{ route('admin.overtime-requests.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-900/20 transition hover:bg-violet-700">
                <i class="bi bi-plus-lg"></i>
                Tạo đơn
            </a>
        </div>

        <x-flash-messages />

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.overtime-requests.department',
        ])

        @include('admin.overtime-requests.partials.list-body', [
            'showDepartmentColumn' => true,
            'scopeLabel' => 'Toàn công ty',
        ])
    </div>
</x-admin-layout>
