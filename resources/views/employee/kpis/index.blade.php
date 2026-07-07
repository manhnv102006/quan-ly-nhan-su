@php
    $navigation = \App\Support\EmployeeNavigation::items();
@endphp

<x-staff-layout
    title="KPI của tôi"
    subtitle="Theo dõi và cập nhật tiến độ mục tiêu được giao."
    role="employee"
    :navigation="$navigation"
>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">KPI của tôi</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $employeeKpis->total() }} mục tiêu được giao
                </p>
            </div>
        </div>

        {{-- Danh sách --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách mục tiêu KPI</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã KPI</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mục tiêu</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Tiến độ</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Hạn chót</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Điểm</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhận xét</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Người giao</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeKpis as $employeeKpi)
                            @php $progress = max(0, min(100, (int) ($employeeKpi->progress ?? 0))); @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition align-top">
                                <td class="px-5 py-4 font-mono font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $employeeKpi->kpi->code ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $employeeKpi->target }}</p>
                                    @if($employeeKpi->comment)
                                        <p class="text-xs text-slate-500 mt-1 max-w-xs">{{ Str::limit($employeeKpi->comment, 90) }}</p>
                                    @endif
                                    @if($employeeKpi->kpi && $employeeKpi->kpi->tasks->isNotEmpty())
                                        <div class="mt-2 max-w-xs">
                                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Nhiệm vụ</p>
                                            <ul class="mt-1 space-y-1">
                                                @foreach($employeeKpi->kpi->tasks as $task)
                                                    <li class="flex gap-1.5 text-xs text-slate-600">
                                                        <span class="text-sky-500">•</span>
                                                        <span>
                                                            {{ $task->title }}
                                                            @if($task->description)
                                                                <span class="block text-[11px] text-slate-400">{{ Str::limit($task->description, 70) }}</span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="w-32">
                                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                                            <span class="font-semibold text-slate-700">{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-500"
                                                 style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center text-slate-600 whitespace-nowrap">
                                    {{ optional($employeeKpi->deadline)->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium
                                        @class([
                                            'bg-amber-100 text-amber-700' => $employeeKpi->status === 'pending',
                                            'bg-blue-100 text-blue-700' => $employeeKpi->status === 'in_progress',
                                            'bg-green-100 text-green-700' => $employeeKpi->status === 'completed',
                                            'bg-red-100 text-red-700' => $employeeKpi->status === 'not_completed',
                                        ])">
                                        {{ $employeeKpi->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($employeeKpi->score !== null)
                                        <span class="font-semibold text-slate-800">{{ number_format($employeeKpi->score, 0) }}</span>
                                        <span class="text-slate-400">/100</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Chưa chấm</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    @if($employeeKpi->review)
                                        <p class="max-w-xs">{{ Str::limit($employeeKpi->review, 90) }}</p>
                                    @else
                                        <span class="text-slate-400 text-xs">Chưa có nhận xét</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $employeeKpi->kpiAssignment->manager->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($employeeKpi->score === null && $employeeKpi->status !== 'not_completed')
                                        <a href="{{ route('employee.kpis.edit', $employeeKpi) }}"
                                           class="inline-flex items-center gap-1 px-3 py-2 bg-sky-600 text-white text-xs font-medium rounded-lg hover:bg-sky-700 transition">
                                            Cập nhật
                                        </a>
                                    @elseif($employeeKpi->score !== null)
                                        <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-medium">
                                            ✓ Đã đánh giá
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">Đã khóa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    Bạn chưa được giao mục tiêu KPI nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employeeKpis->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                    {{ $employeeKpis->links() }}
                </div>
            @endif
        </div>

    </div>
</x-staff-layout>
