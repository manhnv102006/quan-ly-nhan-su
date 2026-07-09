<x-admin-layout title="Cập nhật giao KPI">
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.kpi-assignments.index') }}" class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition hover:bg-slate-200">←</a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Cập nhật giao KPI</h2>
                <p class="mt-1 text-sm text-slate-500">Đổi KPI hoặc manager — mục tiêu và thời gian theo KPI đã chọn</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-8 py-6">
                <h3 class="font-semibold text-slate-800">Thông tin giao KPI</h3>
            </div>

            <form action="{{ route('admin.kpi-assignments.update', $assignment) }}" method="POST" class="space-y-6 p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="kpi_id" class="mb-2 block text-sm font-semibold text-slate-800">
                            KPI <span class="text-red-500">*</span>
                        </label>
                        <select id="kpi_id" name="kpi_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/30">
                            <option value="">-- Chọn KPI --</option>
                            @foreach ($kpis as $kpi)
                                <option value="{{ $kpi->id }}"
                                    data-departments="{{ implode(',', $kpi->departments->pluck('id')->all()) }}"
                                    data-departments-label="{{ $kpi->departments->pluck('department_name')->join(', ') ?: '—' }}"
                                    data-positions-label="{{ implode(', ', $kpi->position_labels) ?: '—' }}"
                                    data-target-value="{{ $kpi->numericTargetForAssignment() ?? '' }}"
                                    data-target-display="{{ $kpi->formattedTargetDisplay() }}"
                                    data-start-date="{{ optional($kpi->start_date)->format('Y-m-d') }}"
                                    data-end-date="{{ optional($kpi->end_date)->format('Y-m-d') }}"
                                    @selected($assignment->kpi_id == $kpi->id || old('kpi_id') == $kpi->id)>
                                    {{ $kpi->code }} - {{ $kpi->title }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('kpi_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="manager_id" class="mb-2 block text-sm font-semibold text-slate-800">
                            Manager <span class="text-red-500">*</span>
                        </label>
                        <select id="manager_id" name="manager_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/30">
                            <option value="">-- Chọn Manager --</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    data-departments="{{ optional($manager->employee)->department_id }}"
                                    @selected($assignment->manager_id == $manager->id || old('manager_id') == $manager->id)>
                                    {{ $manager->name }} ({{ $manager->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
                        <p id="manager_empty" class="mt-2 hidden text-sm text-red-500">Không có manager nào thuộc phòng ban áp dụng của KPI này.</p>
                    </div>
                </div>

                @include('admin.kpi-assignments.partials.kpi-details-preview')

                <div>
                    <label for="note" class="mb-2 block text-sm font-semibold text-slate-800">Ghi chú</label>
                    <textarea id="note" name="note" rows="4" placeholder="Ghi chú thêm khi giao KPI (tuỳ chọn)..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/30">{{ old('note', $assignment->note) }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-2" />
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-violet-600 px-6 py-3 font-medium text-white shadow-lg shadow-violet-500/20 transition hover:bg-violet-700">
                        Cập nhật
                    </button>
                    <a href="{{ route('admin.kpi-assignments.index') }}" class="rounded-xl bg-slate-200 px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-300">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('admin.kpi-assignments.partials.form-script')
</x-admin-layout>
