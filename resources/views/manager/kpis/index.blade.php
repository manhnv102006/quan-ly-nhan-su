<x-manager-layout
    title="KPI được giao"
    subtitle="Các KPI bạn phụ trách và giao cho nhân viên trong phòng ban."
>
    <div class="manager-page">
        <section class="manager-hero">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">Quản lý hiệu suất</span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight">KPI phòng ban</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-100/90">
                        Theo dõi các chỉ tiêu được giao, phân bổ mục tiêu cho nhân viên và chấm điểm tiến độ.
                    </p>
                </div>
            </div>
        </section>

        <div class="manager-panel">
            <div class="manager-panel-header flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="manager-kicker">Danh sách</p>
                    <h3 class="manager-section-title text-lg">KPI được giao ({{ $assignments->total() }})</h3>
                    <p class="manager-section-subtitle">Các KPI bạn cần phụ trách và giao cho nhân viên</p>
                </div>
            </div>

            <div class="manager-table-wrap overflow-x-auto">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>Mã KPI</th>
                            <th>Tên KPI</th>
                            <th>Deadline</th>
                            <th class="text-center">Đã giao</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Người giao</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $assignment->kpi->code ?? 'N/A' }}</td>
                                <td>
                                    <p class="font-semibold text-slate-800">{{ $assignment->kpi->title ?? 'N/A' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ Str::limit($assignment->kpi->description ?? '', 100) }}</p>
                                </td>
                                <td>{{ $assignment->end_date->format('d/m/Y') }}</td>
                                <td class="text-center font-semibold">{{ $assignment->employee_kpis_count }}</td>
                                <td class="text-center">
                                    <span class="manager-badge {{ $assignment->status_color }}">{{ $assignment->status_label }}</span>
                                </td>
                                <td>
                                    {{ $assignment->assignedBy->name ?? 'N/A' }}
                                    <p class="mt-1 text-xs text-slate-500">Ngày giao: {{ $assignment->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <a href="{{ route('manager.kpis.show', $assignment) }}" class="manager-btn-secondary px-3 py-2 text-xs">
                                            Chi tiết
                                        </a>
                                        <a href="{{ route('manager.kpis.assign', $assignment) }}" class="manager-btn-primary px-3 py-2 text-xs">
                                            Giao NV
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="manager-empty-state">
                                        <div class="manager-empty-icon">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z" />
                                            </svg>
                                        </div>
                                        <p class="manager-empty-title">Chưa có KPI nào được giao</p>
                                        <p class="manager-empty-text">Khi admin giao KPI cho bạn, danh sách sẽ hiển thị tại đây.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assignments->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-manager-layout>
