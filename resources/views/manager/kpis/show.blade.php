@php
    $navigation = \App\Support\ManagerNavigation::items();
@endphp

<x-staff-layout
    :title="'Chi tiết KPI: ' . $assignment->kpi_title"
    subtitle="Xem thông tin KPI và các mục tiêu đã giao cho nhân viên."
    role="manager"
    :navigation="$navigation"
>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Chi tiết KPI
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $assignment->kpi_code }} — {{ $assignment->kpi_title }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('manager.kpis.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200 transition">
                    ← Quay lại
                </a>
                <a href="{{ route('manager.kpis.assign', $assignment) }}"
                   class="px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                    + Giao mục tiêu mới
                </a>
            </div>
        </div>

        {{-- Thông tin KPI gốc --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Thông tin KPI được giao cho bạn</h3>
                <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium {{ $assignment->status_color }}">
                    {{ $assignment->status_label }}
                </span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Mã KPI</span>
                    <span class="font-semibold text-slate-800">{{ $assignment->kpi_code }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Người giao</span>
                    <span class="font-semibold text-slate-800">{{ $assignment->assignedBy->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Mục tiêu của bạn</span>
                    <span class="font-semibold text-slate-800">{{ number_format($assignment->target) }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Ngày bắt đầu</span>
                    <span class="font-semibold text-slate-800">{{ $assignment->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="md:col-span-2 flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Ngày kết thúc</span>
                    <span class="font-semibold text-slate-800">{{ $assignment->end_date->format('d/m/Y') }}</span>
                </div>
                <div class="md:col-span-2">
                    <span class="text-slate-500 block mb-1">Mô tả</span>
                    <p class="text-slate-700">{{ $assignment->kpi->description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Danh sách mục tiêu đã giao cho nhân viên --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">
                    Các mục tiêu đã giao cho nhân viên ({{ $assignment->employeeKpis->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mục tiêu</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Điểm</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Hạn chót</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Tiến độ</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignment->employeeKpis as $goal)
                            @php $progress = max(0, min(100, (int) ($goal->progress ?? 0))); @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $goal->employee->full_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500">{{ $goal->employee->employee_code ?? '' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    <p class="font-medium">{{ $goal->target }}</p>
                                    @if($goal->comment)
                                        <p class="text-xs text-slate-500 mt-1">{{ Str::limit($goal->comment, 80) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center font-semibold text-slate-800">
                                    {{ $goal->score !== null ? $goal->score : '—' }}
                                </td>
                                <td class="px-5 py-4 text-center text-slate-600">
                                    {{ $goal->deadline ? $goal->deadline->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="w-32">
                                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                                            <span>{{ $progress }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-violet-500 rounded-full" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium
                                        @class([
                                            'bg-amber-100 text-amber-700' => $goal->status === 'pending',
                                            'bg-blue-100 text-blue-700' => $goal->status === 'in_progress',
                                            'bg-green-100 text-green-700' => $goal->status === 'completed',
                                            'bg-red-100 text-red-700' => $goal->status === 'not_completed',
                                        ])">
                                        {{ $goal->status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('manager.kpis.employee_kpis.score.edit', $goal) }}"
                                       class="inline-flex items-center gap-1 px-3 py-2 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition">
                                        Chấm KPI
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    Chưa có mục tiêu nào được giao cho nhân viên.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-staff-layout>
