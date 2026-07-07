<x-employee-layout
    title="KPI của tôi"
    subtitle="Theo dõi và cập nhật tiến độ mục tiêu được giao."
>
    <div class="employee-page">
        <section class="employee-hero">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">Mục tiêu cá nhân</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight">KPI của tôi</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-sky-100/90">
                        Theo dõi tiến độ, cập nhật kết quả và xem đánh giá từ quản lý trực tiếp.
                    </p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-100">Tổng mục tiêu</p>
                    <p class="mt-2 text-3xl font-extrabold">{{ $employeeKpis->total() }}</p>
                </div>
            </div>
        </section>

        <div class="employee-panel">
            <div class="employee-panel-header">
                <div>
                    <p class="employee-kicker">Danh sách</p>
                    <h3 class="employee-section-title text-lg">Mục tiêu KPI được giao</h3>
                    <p class="employee-section-subtitle">Cập nhật tiến độ trước hạn chót để quản lý có thể đánh giá kịp thời</p>
                </div>
            </div>

            <div class="employee-table-wrap overflow-x-auto">
                <table class="employee-table text-sm">
                    <thead>
                        <tr>
                            <th>Mã KPI</th>
                            <th>Mục tiêu</th>
                            <th>Tiến độ</th>
                            <th class="text-center">Hạn chót</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Điểm</th>
                            <th>Nhận xét</th>
                            <th>Người giao</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeKpis as $employeeKpi)
                            @php $progress = max(0, min(100, (int) ($employeeKpi->progress ?? 0))); @endphp
                            <tr class="align-top">
                                <td class="font-mono font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $employeeKpi->kpi->code ?? 'N/A' }}
                                </td>
                                <td>
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
                                <td>
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
                                <td class="text-center text-slate-600 whitespace-nowrap">
                                    {{ optional($employeeKpi->deadline)->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="text-center">
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
                                <td class="text-center whitespace-nowrap">
                                    @if($employeeKpi->score !== null)
                                        <span class="font-semibold text-slate-800">{{ number_format($employeeKpi->score, 0) }}</span>
                                        <span class="text-slate-400">/100</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Chưa chấm</span>
                                    @endif
                                </td>
                                <td class="text-slate-600">
                                    @if($employeeKpi->review)
                                        <p class="max-w-xs">{{ Str::limit($employeeKpi->review, 90) }}</p>
                                    @else
                                        <span class="text-slate-400 text-xs">Chưa có nhận xét</span>
                                    @endif
                                </td>
                                <td class="text-slate-600 whitespace-nowrap">
                                    {{ $employeeKpi->kpiAssignment->manager->name ?? 'N/A' }}
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    @if($employeeKpi->score === null && $employeeKpi->status !== 'not_completed')
                                        <a href="{{ route('employee.kpis.edit', $employeeKpi) }}"
                                           class="employee-btn-primary inline-flex items-center gap-1 px-3 py-2 text-xs">
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
</x-employee-layout>
