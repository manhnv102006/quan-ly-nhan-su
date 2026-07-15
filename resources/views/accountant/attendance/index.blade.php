<x-accountant-layout title="Chấm công" subtitle="Theo dõi chấm công theo phòng ban">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Chấm công</h2>
                <p class="text-sm text-slate-500">Hôm nay: {{ $today }}</p>
            </div>
        </div>

        @php
            $totalPresent = $departments->sum('today_present');
            $totalEmployees = $departments->sum('employees_count');
        @endphp

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Có mặt hôm nay', 'value' => $totalPresent, 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng nhân viên', 'value' => $totalEmployees])
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count(), 'tone' => 'text-indigo-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-emerald-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">Xem ngày công theo tháng</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.attendance.index', ['department_id' => $department->id]) }}"
                       class="group rounded-2xl border border-emerald-100/80 bg-gradient-to-br from-emerald-50/40 to-teal-50/30 p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-emerald-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-emerald-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-emerald-600">{{ $department->today_present }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Có mặt hôm nay</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
