<x-app-layout title="KPI Được Giao">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Danh sách KPI được giao
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Các KPI bạn cần phụ trách và giao cho nhân viên
                </p>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">
                    Danh sách KPI ({{ $assignments->total() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã KPI</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên KPI</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Deadline</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Đã giao</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Người giao</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $assignment->kpi->code ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                <p class="font-semibold text-slate-800">{{ $assignment->kpi->title ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ Str::limit($assignment->kpi->description ?? '', 100) }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $assignment->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600 font-semibold">
                                {{ $assignment->employee_kpis_count }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium {{ $assignment->status_color }}">
                                    {{ $assignment->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $assignment->assignedBy->name ?? 'N/A' }}
                                <p class="text-xs text-slate-500 mt-1">Ngày giao: {{ $assignment->created_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="{{ route('manager.kpis.show', $assignment) }}" class="px-3 py-2 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition">
                                    Xem chi tiết
                                </a>
                                <a href="{{ route('manager.kpis.assign', $assignment) }}" class="px-3 py-2 bg-violet-600 text-white text-xs font-medium rounded-lg hover:bg-violet-700 transition">
                                    Giao cho nhân viên
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                Bạn chưa được giao KPI nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($assignments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="text-sm text-slate-500">
                    Hiển thị <span class="font-semibold">{{ $assignments->firstItem() }}</span> đến <span class="font-semibold">{{ $assignments->lastItem() }}</span> trong tổng số <span class="font-semibold">{{ $assignments->total() }}</span> bản ghi
                </div>
                <nav class="flex items-center gap-1">
                    @if ($assignments->onFirstPage())
                    <span class="px-3 py-2 rounded-lg text-slate-400 bg-slate-100 text-sm">← Trước</span>
                    @else
                    <a href="{{ $assignments->previousPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 text-sm">← Trước</a>
                    @endif

                    @if ($assignments->hasMorePages())
                    <a href="{{ $assignments->nextPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 text-sm">Tiếp →</a>
                    @else
                    <span class="px-3 py-2 rounded-lg text-slate-400 bg-slate-100 text-sm">Tiếp →</span>
                    @endif
                </nav>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
