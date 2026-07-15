<x-accountant-layout title="Hợp đồng" subtitle="Phòng ban → Nhân viên → Hợp đồng">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Hợp đồng lao động</h2>
                <p class="text-sm text-slate-500">Xem mức lương, phụ cấp và trạng thái hợp đồng theo phòng ban.</p>
            </div>
        </div>

        @php
            $totalActive = $departments->sum('active_contracts_count');
            $totalContracts = $departments->sum('contracts_count');
            $totalEmployees = $departments->sum('employees_count');
        @endphp

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count()])
            @include('accountant.partials.stat-card', ['label' => 'HĐ hiệu lực', 'value' => $totalActive, 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng hợp đồng', 'value' => $totalContracts, 'tone' => 'text-rose-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban · {{ $totalEmployees }} nhân viên</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.contracts.index', ['department_id' => $department->id]) }}"
                       class="group rounded-2xl border border-amber-100/80 bg-gradient-to-br from-amber-50/40 to-orange-50/30 p-5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-amber-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-amber-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-emerald-600">{{ $department->active_contracts_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Hiệu lực</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-rose-600">{{ $department->contracts_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Tổng HĐ</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
