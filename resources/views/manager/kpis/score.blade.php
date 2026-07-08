<x-manager-layout
    :title="'Chấm KPI cho ' . ($employeeKpi->employee->full_name ?? '')"
    subtitle="Nhập điểm và nhận xét cho mục tiêu KPI của nhân viên."
>
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chấm KPI</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Nhân viên: {{ $employeeKpi->employee->full_name ?? 'N/A' }}
                </p>
            </div>

            <a href="{{ route('manager.kpis.index') }}"
               class="manager-btn-secondary">
                ← Quay lại
            </a>
        </div>

        {{-- Thông tin KPI --}}
        <div class="manager-panel">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Thông tin mục tiêu</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Nhân viên</span>
                    <span class="font-semibold text-slate-800">{{ $employeeKpi->employee->full_name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Mã nhân viên</span>
                    <span class="font-semibold text-slate-800">{{ $employeeKpi->employee->employee_code ?? '—' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Tên mục tiêu</span>
                    <span class="font-semibold text-slate-800">{{ $employeeKpi->target }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Hạn chót</span>
                    <span class="font-semibold text-slate-800">{{ optional($employeeKpi->deadline)->format('d/m/Y') ?? '—' }}</span>
                </div>
                @php $progress = max(0, min(100, (int) ($employeeKpi->progress ?? 0))); @endphp
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Tiến độ hiện tại</span>
                    <span class="font-semibold text-slate-800">{{ $progress }}%</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Trạng thái</span>
                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium
                        @class([
                            'bg-amber-100 text-amber-700' => $employeeKpi->status === 'pending',
                            'bg-blue-100 text-blue-700' => $employeeKpi->status === 'in_progress',
                            'bg-green-100 text-green-700' => $employeeKpi->status === 'completed',
                            'bg-red-100 text-red-700' => $employeeKpi->status === 'not_completed',
                        ])">
                        {{ $employeeKpi->status_label ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form chấm điểm --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <h3 class="font-semibold text-slate-800 mb-5">Nhập điểm &amp; nhận xét</h3>

            <form method="POST" action="{{ route('manager.kpis.employee_kpis.score.update', $employeeKpi) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="score" class="block text-sm font-semibold text-slate-700 mb-2">
                        Điểm (0–100) <span class="text-red-500">*</span>
                    </label>
                    <input id="score" type="number" name="score" min="0" max="100" required
                        value="{{ old('score', $employeeKpi->score !== null ? (int) $employeeKpi->score : '') }}"
                        class="w-full rounded-xl border @error('score') border-red-400 @else border-slate-300 @enderror focus:border-teal-500 focus:ring-teal-500">
                    @error('score')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="review" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nhận xét
                    </label>
                    <textarea id="review" name="review" rows="4"
                        placeholder="Nhập nhận xét cho nhân viên..."
                        class="w-full rounded-xl border @error('review') border-red-400 @else border-slate-300 @enderror focus:border-teal-500 focus:ring-teal-500">{{ old('review', $employeeKpi->review) }}</textarea>
                    @error('review')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('manager.kpis.index') }}"
                       class="px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-medium shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition">
                        Lưu điểm
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-manager-layout>
