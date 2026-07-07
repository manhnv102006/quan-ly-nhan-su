@php
    $isLocked = $employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED;
    $currentProgress = max(0, min(100, (int) ($employeeKpi->progress ?? 0)));
@endphp

<x-employee-layout
    :title="'Cập nhật tiến độ: ' . $employeeKpi->target"
    subtitle="Cập nhật tiến độ và trạng thái KPI của bạn."
>
    <div class="employee-page" x-data="{ progress: {{ old('progress', $currentProgress) }} }">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Cập nhật tiến độ KPI</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $employeeKpi->target }}</p>
            </div>
            <a href="{{ route('employee.kpis.index') }}"
               class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200 transition">
                ← Quay lại
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form cập nhật --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-semibold text-slate-800">Form cập nhật</h3>
                    </div>

                    <div class="p-6">
                        @if ($isLocked)
                            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.008v.008H12v-.008z" />
                                </svg>
                                <p class="text-sm text-red-700">
                                    KPI này đã quá hạn và được chuyển sang trạng thái <strong>Không hoàn thành</strong>. Bạn không thể chỉnh sửa nữa.
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('employee.kpis.update', $employeeKpi) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')

                            {{-- Tiến độ --}}
                            <div>
                                <label for="progress" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Tiến độ (%)
                                </label>

                                <div class="flex items-center gap-4">
                                    <input type="range" min="0" max="100" step="1"
                                        x-model="progress"
                                        @disabled($isLocked)
                                        class="flex-1 accent-sky-600 disabled:opacity-50">
                                    <div class="w-20">
                                        <input type="number" name="progress" id="progress"
                                            x-model="progress"
                                            min="0" max="100" step="1"
                                            @disabled($isLocked)
                                            required
                                            class="w-full rounded-xl border @error('progress') border-red-400 @else border-slate-300 @enderror focus:border-sky-500 focus:ring-sky-500 text-center">
                                    </div>
                                </div>

                                <div class="mt-3 w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-sky-500 to-indigo-500 rounded-full transition-all"
                                         :style="'width: ' + progress + '%'"></div>
                                </div>

                                @error('progress')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Trạng thái --}}
                            <div>
                                <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Trạng thái
                                </label>

                                <select name="status" id="status" required
                                    @disabled($isLocked)
                                    class="w-full rounded-xl border @error('status') border-red-400 @else border-slate-300 @enderror focus:border-sky-500 focus:ring-sky-500 disabled:bg-slate-50 disabled:text-slate-400">
                                    @if ($isLocked)
                                        <option selected>Không hoàn thành</option>
                                    @endif

                                    @foreach($statusOptions as $value => $label)
                                        @php
                                            $disabled = false;
                                            if (
                                                $employeeKpi->status === \App\Models\EmployeeKPI::STATUS_IN_PROGRESS &&
                                                $value === \App\Models\EmployeeKPI::STATUS_PENDING
                                            ) {
                                                $disabled = true;
                                            }
                                            if (
                                                $employeeKpi->status === \App\Models\EmployeeKPI::STATUS_COMPLETED &&
                                                in_array($value, [
                                                    \App\Models\EmployeeKPI::STATUS_PENDING,
                                                    \App\Models\EmployeeKPI::STATUS_IN_PROGRESS,
                                                ])
                                            ) {
                                                $disabled = true;
                                            }
                                        @endphp

                                        <option value="{{ $value }}"
                                            {{ old('status', $employeeKpi->status) == $value ? 'selected' : '' }}
                                            @disabled($disabled)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <a href="{{ route('employee.kpis.index') }}"
                                   class="px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                                    Hủy
                                </a>
                                <button type="submit"
                                    @disabled($isLocked)
                                    class="px-6 py-3 rounded-xl bg-sky-600 text-white font-medium shadow-lg shadow-sky-500/20 hover:bg-sky-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Lưu tiến độ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Thông tin mục tiêu --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-semibold text-slate-800">Thông tin mục tiêu</h3>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <div>
                            <p class="text-slate-500 mb-0.5">Tên mục tiêu</p>
                            <p class="font-semibold text-slate-800">{{ $employeeKpi->target }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-0.5">Mã KPI</p>
                            <p class="font-semibold text-slate-800">{{ $employeeKpi->kpi->code ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-0.5">Tên KPI gốc</p>
                            <p class="font-semibold text-slate-800">{{ $employeeKpi->kpi->title ?? 'N/A' }}</p>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-slate-500 mb-0.5">Người giao</p>
                            <p class="font-semibold text-slate-800">{{ $employeeKpi->kpiAssignment->manager->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-0.5">Hạn chót</p>
                            <p class="font-semibold text-slate-800">{{ optional($employeeKpi->deadline)->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-1">Trạng thái hiện tại</p>
                            <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium
                                @class([
                                    'bg-amber-100 text-amber-700' => $employeeKpi->status === 'pending',
                                    'bg-blue-100 text-blue-700' => $employeeKpi->status === 'in_progress',
                                    'bg-green-100 text-green-700' => $employeeKpi->status === 'completed',
                                    'bg-red-100 text-red-700' => $employeeKpi->status === 'not_completed',
                                ])">
                                {{ $employeeKpi->status_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-employee-layout>
