<x-admin-layout title="Báo cáo chấm công">

    <div class="space-y-6">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Báo cáo chấm công</h1>
            <p class="mt-1 text-sm text-slate-500">
                Tháng {{ $month }}/{{ $year }} —
                <span class="font-semibold text-slate-700">Toàn công ty</span>
            </p>
        </div>

        {{-- Lọc tháng/năm --}}
        @include('admin.attendance-reports.partials.report-filter', [
            'filterAction' => route('admin.attendance-reports.index'),
        ])

        {{-- Ô phòng ban — bấm để xem chi tiết --}}
        <section class="space-y-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Theo phòng ban</p>
                <h2 class="mt-1 text-lg font-bold text-slate-800">Bấm vào phòng ban để xem báo cáo chi tiết</h2>
            </div>

            @if ($departmentSummaries->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                    Chưa có phòng ban nào.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($departmentSummaries as $summary)
                        @php
                            $dept = $summary['department'];
                            $deptStats = $summary['stats'];
                            $url = route('admin.attendance-reports.department', [
                                'department' => $dept->id,
                                'month' => $month,
                                'year' => $year,
                            ]);
                        @endphp
                        <a href="{{ $url }}"
                           class="group block rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-violet-300 hover:shadow-md hover:-translate-y-0.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-sm font-bold text-white shadow-sm">
                                    {{ strtoupper(mb_substr($dept->department_name, 0, 1)) }}
                                </div>
                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-violet-600 group-hover:bg-violet-100">
                                    Xem báo cáo →
                                </span>
                            </div>

                            <h3 class="mt-4 font-bold text-slate-800 group-hover:text-violet-700 transition">
                                {{ $dept->department_name }}
                            </h3>
                            <p class="text-xs text-slate-500">{{ $dept->department_code }}</p>

                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-xl bg-slate-50 px-2 py-2">
                                    <p class="text-lg font-extrabold text-slate-800">{{ $deptStats['employee_count'] }}</p>
                                    <p class="text-[10px] text-slate-500">NV chấm công</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 px-2 py-2">
                                    <p class="text-lg font-extrabold text-emerald-600">{{ $deptStats['present'] + $deptStats['late'] }}</p>
                                    <p class="text-[10px] text-slate-500">Đi làm</p>
                                </div>
                                <div class="rounded-xl bg-amber-50 px-2 py-2">
                                    <p class="text-lg font-extrabold text-amber-600">{{ $deptStats['late'] }}</p>
                                    <p class="text-[10px] text-slate-500">Đi muộn</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Báo cáo toàn công ty (phần thống kê + bảng) --}}
        <section class="space-y-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Tổng hợp</p>
                <h2 class="mt-1 text-lg font-bold text-slate-800">Báo cáo toàn công ty</h2>
            </div>

            @include('admin.attendance-reports.partials.report-body')
        </section>
    </div>

</x-admin-layout>
