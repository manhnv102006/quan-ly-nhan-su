<x-admin-layout title="Giao KPI">

    <div class="max-w-3xl mx-auto">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                Giao KPI cho nhân viên
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Chọn mẫu KPI, hệ thống sẽ tự lọc nhân viên thuộc đúng phòng ban và chức vụ áp dụng
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">

            <form action="{{ route('admin.employee-kpis.store') }}" method="POST" class="space-y-6">

                @csrf

                {{-- Mẫu KPI --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Mẫu KPI <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="kpi_id"
                        id="kpi-select"
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                        <option value="">-- Chọn KPI đã tạo --</option>
                        @foreach($kpis as $kpi)
                            <option
                                value="{{ $kpi->id }}"
                                data-departments="{{ implode(',', $kpi->departments->pluck('id')->all()) }}"
                                data-positions="{{ implode(',', $kpi->positions ?? []) }}"
                                data-target="{{ $kpi->target }}"
                                data-deadline="{{ optional($kpi->end_date)->format('d/m/Y') }}"
                                {{ old('kpi_id') == $kpi->id ? 'selected' : '' }}>
                                {{ $kpi->code ? $kpi->code . ' - ' : '' }}{{ $kpi->title }}
                            </option>
                        @endforeach
                    </select>

                    @error('kpi_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Thông tin mẫu KPI --}}
                    <div id="kpi-info" class="hidden mt-3 p-4 rounded-xl bg-violet-50 text-sm text-slate-600 space-y-1">
                        <p><span class="font-semibold text-slate-700">Mục tiêu:</span> <span id="kpi-target">-</span></p>
                        <p><span class="font-semibold text-slate-700">Hạn hoàn thành:</span> <span id="kpi-deadline">-</span></p>
                    </div>
                </div>

                {{-- Nhân viên --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Nhân viên <span class="text-red-500">*</span>
                    </label>

                    <select
                        name="employee_id"
                        id="employee-select"
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                        disabled>
                        <option value="">-- Vui lòng chọn mẫu KPI trước --</option>
                        @foreach($employees as $employee)
                            <option
                                value="{{ $employee->id }}"
                                data-department="{{ $employee->department_id }}"
                                data-role="{{ $employee->user?->role?->name }}"
                                {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }}
                                @if($employee->department) - {{ $employee->department->department_name }} @endif
                            </option>
                        @endforeach
                    </select>

                    <p id="employee-empty" class="hidden text-amber-600 text-sm mt-1">
                        Không có nhân viên nào phù hợp với phòng ban / chức vụ của KPI này.
                    </p>

                    @error('employee_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Ghi chú
                    </label>

                    <textarea
                        name="note"
                        rows="3"
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                        placeholder="Ghi chú thêm nếu cần">{{ old('note') }}</textarea>

                    @error('note')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">

                    <a href="{{ route('admin.employee-kpis.index') }}"
                       class="px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Giao KPI
                    </button>

                </div>

            </form>

        </div>

    </div>

    <script>
        (function () {
            const kpiSelect = document.getElementById('kpi-select');
            const employeeSelect = document.getElementById('employee-select');
            const employeeOptions = Array.from(employeeSelect.options).filter(o => o.value !== '');
            const placeholderOption = employeeSelect.querySelector('option[value=""]');
            const kpiInfo = document.getElementById('kpi-info');
            const kpiTarget = document.getElementById('kpi-target');
            const kpiDeadline = document.getElementById('kpi-deadline');
            const emptyMsg = document.getElementById('employee-empty');

            function parseList(value) {
                return (value || '')
                    .split(',')
                    .map(v => v.trim())
                    .filter(v => v !== '');
            }

            function refreshEmployees() {
                const selected = kpiSelect.options[kpiSelect.selectedIndex];

                if (!selected || !selected.value) {
                    employeeSelect.disabled = true;
                    placeholderOption.textContent = '-- Vui lòng chọn mẫu KPI trước --';
                    employeeOptions.forEach(o => { o.hidden = true; o.disabled = true; });
                    employeeSelect.value = '';
                    kpiInfo.classList.add('hidden');
                    emptyMsg.classList.add('hidden');
                    return;
                }

                const departments = parseList(selected.dataset.departments);
                const positions = parseList(selected.dataset.positions);

                let visibleCount = 0;
                employeeOptions.forEach(function (o) {
                    const dept = (o.dataset.department || '').trim();
                    const role = (o.dataset.role || '').trim();

                    const deptOk = departments.length === 0 || departments.includes(dept);
                    const roleOk = positions.length === 0 || positions.includes(role);
                    const match = deptOk && roleOk;

                    o.hidden = !match;
                    o.disabled = !match;
                    if (match) visibleCount++;
                    if (!match && o.selected) { o.selected = false; }
                });

                employeeSelect.disabled = false;
                placeholderOption.textContent = '-- Chọn nhân viên --';

                kpiTarget.textContent = selected.dataset.target || 'Chưa thiết lập';
                kpiDeadline.textContent = selected.dataset.deadline || 'Không có';
                kpiInfo.classList.remove('hidden');

                emptyMsg.classList.toggle('hidden', visibleCount > 0);
            }

            kpiSelect.addEventListener('change', refreshEmployees);
            refreshEmployees();
        })();
    </script>

</x-admin-layout>
