<x-manager-layout
    title="KPI được giao"
    subtitle="Các KPI bạn phụ trách và giao cho nhân viên trong phòng ban."
>
    @php
        $cardsData = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'code' => $assignment->kpi->code ?? 'N/A',
                'title' => $assignment->kpi->title ?? 'N/A',
                'description' => $assignment->kpi->description ?? '',
                'target' => number_format((float) $assignment->target),
                'start_date' => $assignment->start_date?->format('d/m/Y'),
                'end_date' => $assignment->end_date?->format('d/m/Y'),
                'status_label' => $assignment->status_label,
                'status_tailwind' => $assignment->status_tailwind,
                'assigned_by' => $assignment->assignedBy->name ?? 'N/A',
                'assigned_at' => $assignment->created_at?->format('d/m/Y'),
                'can_assign' => $managedEmployees
                    ->whereNotIn('id', $assignment->employeeKpis->pluck('employee_id'))
                    ->isNotEmpty(),
                'show_url' => route('manager.kpis.show', $assignment),
                'assign_url' => route('manager.kpis.assign', $assignment),
                'tasks' => ($assignment->kpi->tasks ?? collect())->map(fn ($task) => [
                    'title' => $task->title,
                    'description' => $task->description,
                ])->values()->all(),
                'employees' => $assignment->employeeKpis->map(fn ($goal) => [
                    'name' => $goal->employee?->full_name ?? 'N/A',
                    'code' => $goal->employee?->employee_code ?? '',
                    'target' => $goal->target,
                    'comment' => $goal->comment,
                    'deadline' => $goal->deadline?->format('d/m/Y'),
                    'progress' => max(0, min(100, (int) ($goal->progress ?? 0))),
                    'score' => $goal->score,
                    'status_label' => $goal->status_label,
                    'score_url' => route('manager.kpis.employee_kpis.score.edit', $goal),
                ])->values()->all(),
            ];
        })->values();
    @endphp

    <div
        class="manager-page"
        x-data="{
            open: false,
            selected: null,
            cards: @js($cardsData),
            openCard(id) {
                this.selected = this.cards.find((item) => item.id === id) ?? null;
                this.open = this.selected !== null;
                document.body.style.overflow = this.open ? 'hidden' : '';
            },
            closeModal() {
                this.open = false;
                this.selected = null;
                document.body.style.overflow = '';
            }
        }"
        @keydown.escape.window="closeModal()"
    >
        <section class="manager-hero">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">Quản lý hiệu suất</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight">KPI phòng ban</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-100/90">
                        Bấm vào ô KPI để xem thông tin chi tiết và danh sách nhân viên đang thực hiện.
                    </p>
                </div>
            </div>
        </section>

        <div class="manager-panel p-6">
            <div class="mb-6">
                <p class="manager-kicker">Danh sách</p>
                <h3 class="manager-section-title text-lg">KPI được giao ({{ $assignments->total() }})</h3>
                <p class="manager-section-subtitle">Chọn một KPI để xem chi tiết</p>
            </div>

            @if ($assignments->isEmpty())
                <div class="manager-empty-state py-16">
                    <div class="manager-empty-icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z" />
                        </svg>
                    </div>
                    <p class="manager-empty-title">Chưa có KPI nào được giao</p>
                    <p class="manager-empty-text">Khi admin giao KPI cho bạn, danh sách sẽ hiển thị tại đây.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($assignments as $assignment)
                        <button
                            type="button"
                            @click="openCard({{ $assignment->id }})"
                            class="group flex aspect-square w-full flex-col rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-teal-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-400/40"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700">
                                    {{ $assignment->kpi->code ?? 'N/A' }}
                                </span>
                                <span class="manager-badge shrink-0 {{ $assignment->status_color }} text-[10px]">
                                    {{ $assignment->status_label }}
                                </span>
                            </div>

                            <h4 class="mt-4 line-clamp-2 text-base font-bold text-slate-800 group-hover:text-teal-700">
                                {{ $assignment->kpi->title ?? 'N/A' }}
                            </h4>

                            <p class="mt-2 line-clamp-2 flex-1 text-xs leading-relaxed text-slate-500">
                                {{ $assignment->kpi->description ?: 'Không có mô tả' }}
                            </p>

                            <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-xs">
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Deadline</span>
                                    <span class="font-semibold text-slate-700">{{ $assignment->end_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-500">
                                    <span>Thành viên</span>
                                    @if ($assignment->employee_kpis_count > 0)
                                        <span class="font-semibold text-slate-700">{{ $assignment->employee_kpis_count }} người</span>
                                    @else
                                        <span class="font-medium text-amber-600">Chưa có</span>
                                    @endif
                                </div>
                            </div>

                            <p class="mt-3 text-[11px] font-medium text-teal-600 opacity-0 transition group-hover:opacity-100">
                                Bấm để xem chi tiết →
                            </p>
                        </button>
                    @endforeach
                </div>

                @if ($assignments->hasPages())
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        {{ $assignments->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Modal chi tiết KPI --}}
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
            style="display: none;"
        >
            <div
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="closeModal()"
            ></div>

            <div
                x-show="open"
                x-transition
                class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
                @click.stop
            >
                <template x-if="selected">
                    <div class="flex min-h-0 flex-1 flex-col">
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-5 text-white">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-teal-100" x-text="selected.code"></p>
                                <h3 class="mt-1 text-xl font-bold" x-text="selected.title"></h3>
                                <p class="mt-2 text-sm text-teal-50/90 line-clamp-2" x-text="selected.description || 'Không có mô tả'"></p>
                            </div>
                            <button type="button" @click="closeModal()" class="rounded-xl bg-white/15 p-2 hover:bg-white/25" aria-label="Đóng">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Mục tiêu</p>
                                    <p class="mt-1 font-bold text-slate-800" x-text="selected.target"></p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Bắt đầu</p>
                                    <p class="mt-1 font-bold text-slate-800" x-text="selected.start_date"></p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Kết thúc</p>
                                    <p class="mt-1 font-bold text-slate-800" x-text="selected.end_date"></p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 col-span-2 sm:col-span-2">
                                    <p class="text-xs text-slate-500">Người giao</p>
                                    <p class="mt-1 font-bold text-slate-800">
                                        <span x-text="selected.assigned_by"></span>
                                        <span class="text-xs font-normal text-slate-500" x-text="' · ' + selected.assigned_at"></span>
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Trạng thái</p>
                                    <p class="mt-1 font-bold text-slate-800" x-text="selected.status_label"></p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Nhiệm vụ KPI</h4>
                                <ul class="mt-3 space-y-2" x-show="selected.tasks.length > 0">
                                    <template x-for="(task, index) in selected.tasks" :key="index">
                                        <li class="flex gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 text-sm">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700" x-text="index + 1"></span>
                                            <div>
                                                <p class="font-semibold text-slate-800" x-text="task.title"></p>
                                                <p class="text-xs text-slate-500" x-show="task.description" x-text="task.description"></p>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                                <p class="mt-2 text-sm text-slate-400" x-show="selected.tasks.length === 0">Chưa có nhiệm vụ chi tiết.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Nhân viên thực hiện</h4>
                                <div class="mt-3 space-y-3" x-show="selected.employees.length > 0">
                                    <template x-for="(emp, index) in selected.employees" :key="index">
                                        <div class="rounded-2xl border border-slate-200 p-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <p class="font-bold text-slate-800" x-text="emp.name"></p>
                                                    <p class="text-xs text-slate-500" x-text="emp.code"></p>
                                                </div>
                                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600" x-text="emp.status_label"></span>
                                            </div>
                                            <p class="mt-2 text-sm font-medium text-slate-700" x-text="emp.target"></p>
                                            <p class="mt-1 text-xs text-slate-500" x-show="emp.comment" x-text="emp.comment"></p>
                                            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                                <span>Hạn: <strong class="text-slate-700" x-text="emp.deadline || '—'"></strong></span>
                                                <span>Tiến độ: <strong class="text-slate-700" x-text="emp.progress + '%'"></strong></span>
                                                <span>Điểm: <strong class="text-slate-700" x-text="emp.score ?? '—'"></strong></span>
                                            </div>
                                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-teal-500 transition-all" :style="'width:' + emp.progress + '%'"></div>
                                            </div>
                                            <a :href="emp.score_url" class="mt-3 inline-flex rounded-lg bg-emerald-500 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-600">
                                                Chấm KPI
                                            </a>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-3 text-sm text-slate-500" x-show="selected.employees.length === 0">
                                    Chưa giao cho nhân viên nào.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                            <button type="button" @click="closeModal()" class="manager-btn-secondary">Đóng</button>
                            <a :href="selected.show_url" class="manager-btn-secondary">Mở trang chi tiết</a>
                            <a x-show="selected.can_assign" :href="selected.assign_url" class="manager-btn-primary">+ Thêm thành viên</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-manager-layout>
