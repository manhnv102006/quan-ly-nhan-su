<x-manager-layout
    :title="'Chi tiết KPI: ' . $assignment->kpi_title"
    subtitle="Xem thông tin KPI và các mục tiêu đã giao cho nhân viên."
>
    <div class="manager-page">

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
                   class="manager-btn-secondary">
                    ← Quay lại
                </a>
                <a href="{{ route('manager.kpis.assign', $assignment) }}"
                   class="manager-btn-primary">
                    + Giao NV
                </a>
            </div>
        </div>

        {{-- Thông tin KPI gốc --}}
        <div class="manager-panel">
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

        {{-- Nhiệm vụ cần thực hiện của KPI --}}
        <div class="manager-panel">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Nhiệm vụ cần thực hiện</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Các đầu việc cụ thể của KPI này</p>
                </div>
            </div>
            <div class="p-6">
                @forelse ($assignment->kpi?->tasks ?? [] as $index => $task)
                    <div class="flex gap-3 {{ ! $loop->last ? 'mb-3 pb-3 border-b border-slate-50' : '' }}">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $task->title }}</p>
                            @if ($task->description)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $task->description }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">KPI này chưa có nhiệm vụ chi tiết.</p>
                @endforelse
            </div>
        </div>

        {{-- Danh sách mục tiêu đã giao cho nhân viên --}}
        <div class="manager-panel">
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
                                            <div class="h-full bg-teal-500 rounded-full" style="width: {{ $progress }}%"></div>
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
</x-manager-layout>
