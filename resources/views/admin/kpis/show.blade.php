<x-admin-layout title="Chi tiết KPI">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết KPI</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Mã <span class="font-mono font-semibold text-slate-700">{{ $kpi->code }}</span>
                    · {{ $kpi->title }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.kpis.edit', $kpi) }}"
                   class="rounded-xl bg-violet-600 px-5 py-3 font-medium text-white transition hover:bg-violet-700">
                    Sửa KPI
                </a>
                <a href="{{ route('admin.kpis.index') }}"
                   class="rounded-xl bg-slate-200 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-300">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin KPI</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Mã KPI</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono font-semibold text-slate-800">
                                    {{ $kpi->code }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Tên KPI</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800">
                                    {{ $kpi->title }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Trọng số</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-violet-700">
                                    {{ $kpi->weight }}%
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Điểm tối đa</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800">
                                    {{ $kpi->max_score ?? 100 }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Đơn vị đo</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">
                                    {{ $kpi->unit ?: '—' }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Mục tiêu</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800">
                                    {{ $kpi->formattedTargetDisplay() }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Kỳ đánh giá</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">
                                    {{ $kpi->period ? $kpi->period_label : '—' }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Trạng thái</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-medium {{ $kpi->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $kpi->status_label }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Ngày bắt đầu</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">
                                    {{ $kpi->start_date?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm font-medium text-slate-500">Ngày kết thúc</p>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800">
                                    {{ $kpi->end_date?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-sm font-medium text-slate-500">Mô tả</p>
                            <div class="min-h-[100px] whitespace-pre-line rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700">
                                {{ $kpi->description ?: 'Không có mô tả' }}
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-sm font-medium text-slate-500">Phòng ban áp dụng</p>
                            <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                @forelse ($kpi->departments as $department)
                                    <span class="inline-block rounded-lg bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700">
                                        {{ $department->department_name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-400">Chưa gắn phòng ban</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-sm font-medium text-slate-500">Chức vụ áp dụng</p>
                            <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                @forelse ($kpi->position_labels as $label)
                                    <span class="inline-block rounded-lg bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
                                        {{ $label }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-400">Tất cả / chưa giới hạn chức vụ</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Nhiệm vụ / đầu việc</h3>
                        <p class="mt-1 text-sm text-slate-500">Checklist công việc gắn với KPI này</p>
                    </div>
                    <div class="p-6">
                        @if ($kpi->tasks->isEmpty())
                            <p class="text-center text-sm text-slate-400 py-6">Chưa có nhiệm vụ chi tiết</p>
                        @else
                            <ol class="space-y-3">
                                @foreach ($kpi->tasks as $task)
                                    <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ $loop->iteration }}. {{ $task->title }}</p>
                                        @if ($task->description)
                                            <p class="mt-1 text-sm text-slate-600">{{ $task->description }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-violet-100">
                        <span class="text-2xl font-black text-violet-700">{{ $kpi->weight }}%</span>
                    </div>
                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $kpi->title }}</h3>
                    <p class="mt-2 font-mono text-sm text-slate-500">{{ $kpi->code }}</p>
                    <div class="mt-4">
                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-medium {{ $kpi->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $kpi->status_label }}
                        </span>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h4 class="font-semibold text-slate-800">Thống kê</h4>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Lượt giao KPI</dt>
                            <dd class="font-bold text-slate-800">{{ $kpi->assignments_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Đã hoàn thành</dt>
                            <dd class="font-bold text-emerald-700">{{ $kpi->assignments_completed_count ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Số nhiệm vụ</dt>
                            <dd class="font-bold text-slate-800">{{ $kpi->tasks->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Ngày tạo</dt>
                            <dd class="font-medium text-slate-800">{{ $kpi->created_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Cập nhật</dt>
                            <dd class="font-medium text-slate-800">{{ $kpi->updated_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
