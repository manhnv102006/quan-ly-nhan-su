@php
    $isEdit = isset($contract);
    $selectedEmployee = null;

    if (! $isEdit) {
        $currentEmployeeId = (int) old('employee_id', $selectedEmployeeId ?? 0);
        $selectedEmployee = $currentEmployeeId
            ? $employees->firstWhere('id', $currentEmployeeId)
            : null;
    }
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
    <div>
        <label for="employee_id" class="admin-label">Nhân viên *</label>
        @if($isEdit)
            <input type="text" class="admin-field" value="{{ $contract->employee->full_name ?? 'N/A' }}" disabled>
            <input type="hidden" name="employee_id" value="{{ $contract->employee_id }}">
        @else
            <select id="employee_id" name="employee_id" class="admin-field" required data-employee-select>
                <option value="">— Chọn nhân viên —</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}"
                            data-department-id="{{ $employee->department_id }}"
                            data-department-name="{{ $employee->department->department_name ?? '' }}"
                            data-position-id="{{ $employee->position_id }}"
                            data-position-name="{{ $employee->position->position_name ?? '' }}"
                            @selected(old('employee_id', $selectedEmployeeId ?? null) == $employee->id)>
                        {{ $employee->full_name }} ({{ $employee->employee_code }})
                    </option>
                @endforeach
            </select>
        @endif
        @error('employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contract_type_id" class="admin-label">Loại hợp đồng *</label>
        <select id="contract_type_id" name="contract_type_id" class="admin-field" required data-contract-type-select>
            <option value="">— Chọn loại —</option>
            @foreach($contractTypes as $type)
                @php $hints = $type->durationHints(); @endphp
                <option value="{{ $type->id }}"
                        data-internship="{{ $type->isInternship() ? '1' : '0' }}"
                        data-indefinite="{{ $hints['indefinite'] ? '1' : '0' }}"
                        data-months="{{ $hints['months'] }}"
                        data-max-days="{{ $hints['max_days'] }}"
                        data-max-end-months="{{ $hints['max_end_months'] }}"
                        data-max-end-exclusive="{{ $hints['max_end_exclusive'] ? '1' : '0' }}"
                        @selected(old('contract_type_id', $isEdit ? $contract->contract_type_id : null) == $type->id)>
                    {{ $type->contract_name }}
                </option>
            @endforeach
        </select>
        @error('contract_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contract_code" class="admin-label">Mã hợp đồng</label>
        <input type="text" id="contract_code" name="contract_code" class="admin-field"
               value="{{ old('contract_code', $isEdit ? $contract->contract_code : ($nextCode ?? '')) }}"
               placeholder="Để trống sẽ tự sinh">
        @error('contract_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="department_id" class="admin-label">Phòng ban *</label>
        @if($isEdit)
            <select id="department_id" name="department_id" class="admin-field" required data-department-select>
                <option value="">— Chọn phòng ban —</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id', $contract->department_id) == $dept->id)>
                        {{ $dept->department_name }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" id="department_id" class="admin-field bg-slate-100 text-slate-600" readonly
                   data-department-display
                   placeholder="Tự động theo nhân viên"
                   value="{{ $selectedEmployee?->department->department_name ?? '' }}">
            <input type="hidden" name="department_id" data-department-input
                   value="{{ old('department_id', $selectedEmployee?->department_id) }}">
            <p class="mt-1 text-[11px] text-slate-400">Lấy tự động từ hồ sơ nhân viên.</p>
        @endif
        @error('department_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="position_id" class="admin-label">Chức vụ *</label>
        @if($isEdit)
            <select id="position_id" name="position_id" class="admin-field" required data-position-select>
                <option value="">— Chọn chức vụ —</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos->id }}" @selected(old('position_id', $contract->position_id) == $pos->id)>
                        {{ $pos->position_name }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" id="position_id" class="admin-field bg-slate-100 text-slate-600" readonly
                   data-position-display
                   placeholder="Tự động theo nhân viên"
                   value="{{ $selectedEmployee?->position->position_name ?? '' }}">
            <input type="hidden" name="position_id" data-position-input
                   value="{{ old('position_id', $selectedEmployee?->position_id) }}">
            <p class="mt-1 text-[11px] text-slate-400">Lấy tự động từ hồ sơ nhân viên.</p>
        @endif
        @error('position_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="salary" class="admin-label">Lương cơ bản *</label>
        <input type="text" id="salary" name="salary" class="admin-field money-input" inputmode="numeric"
               value="{{ old('salary', $isEdit ? (int) $contract->salary : '') }}" required
               placeholder="VD: 15.000.000">
        @error('salary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    @if(! $isEdit)
        <div class="md:col-span-2 xl:col-span-3 rounded-xl border border-sky-100 bg-sky-50/60 px-4 py-3 text-sm text-sky-900">
            <p class="font-semibold">Quy tắc tạo hợp đồng mới</p>
            <ul class="mt-2 list-disc list-inside space-y-1 text-sky-800">
                <li>Chỉ tạo mới khi nhân viên <strong>chưa có HĐ đang hiệu lực</strong> (đã có thì dùng Gia hạn / Chuyển loại).</li>
                <li>Phòng ban và chức vụ <strong>lấy tự động</strong> theo hồ sơ nhân viên.</li>
                <li>Ngày kết thúc <strong>tự tính</strong> theo thời hạn của loại hợp đồng, tick "Chỉnh tay" nếu muốn nhập khác.</li>
                <li>Thử việc: tối đa 60 ngày · Xác định thời hạn: tối đa 36 tháng · Thời vụ: dưới 12 tháng.</li>
                <li>Không xác định thời hạn: không có ngày kết thúc.</li>
                <li>Trạng thái tự động: ngày bắt đầu ≤ hôm nay → <strong>Còn hiệu lực</strong>; ngày bắt đầu &gt; hôm nay → <strong>Chờ hiệu lực</strong>.</li>
            </ul>
        </div>
    @endif
</div>

@include('admin.contracts.partials.allowance-fields', [
    'allowanceTypes' => $allowanceTypes ?? collect(),
    'allowanceValues' => $allowanceValues ?? [],
    'positions' => $positions ?? collect(),
])

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 mt-4">

    <div>
        <label for="start_date" class="admin-label">Ngày bắt đầu *</label>
        <input type="date" id="start_date" name="start_date" class="admin-field" data-start-date
               value="{{ old('start_date', $isEdit ? $contract->start_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <div class="flex items-center justify-between gap-2">
            <label for="end_date" class="admin-label">Ngày kết thúc</label>
            <label class="mb-1 inline-flex cursor-pointer items-center gap-1.5 text-[11px] font-semibold text-violet-600">
                <input type="checkbox" class="h-3.5 w-3.5 rounded border-slate-300 text-violet-600" data-end-date-manual>
                Chỉnh tay
            </label>
        </div>
        <input type="date" id="end_date" name="end_date" class="admin-field bg-slate-100 text-slate-600" readonly data-end-date
               value="{{ old('end_date', $isEdit ? $contract->end_date?->format('Y-m-d') : '') }}">
        <p class="mt-1 text-[11px] text-slate-400" data-end-date-hint>Tự tính theo thời hạn của loại hợp đồng.</p>
        @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="signed_date" class="admin-label">Ngày ký *</label>
        <input type="date" id="signed_date" name="signed_date" class="admin-field"
               value="{{ old('signed_date', $isEdit ? $contract->signed_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        <p class="mt-1 text-[11px] text-slate-400">Phải nhỏ hơn hoặc bằng ngày bắt đầu.</p>
        @error('signed_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contract_file" class="admin-label">File hợp đồng @if(!$isEdit)*@endif</label>
        <input type="file" id="contract_file" name="contract_file" class="admin-field"
               accept=".pdf,.doc,.docx" @if(!$isEdit) required @endif>
        <p class="mt-1 text-[11px] text-slate-400">PDF, DOC, DOCX · tối đa 10MB</p>
        @if($isEdit && $contract->file_path)
            <p class="mt-1 text-xs text-slate-500">
                Hiện tại:
                <a href="{{ Storage::url($contract->file_path) }}" target="_blank" class="font-medium text-violet-600 hover:text-violet-700">
                    {{ basename($contract->file_path) }}
                </a>
            </p>
        @endif
        @error('contract_file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-4 grid grid-cols-1 gap-4">
    <div>
        <label for="description" class="admin-label">Mô tả *</label>
        <textarea id="description" name="description" rows="2" class="admin-field" required placeholder="Mô tả ngắn">{{ old('description', $isEdit ? $contract->description : '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="note" class="admin-label">Ghi chú nội bộ *</label>
        <textarea id="note" name="note" rows="2" class="admin-field" required placeholder="Ghi chú nội bộ">{{ old('note', $isEdit ? $contract->note : '') }}</textarea>
        @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

@if(! $isEdit)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const employeeSelect = document.querySelector('[data-employee-select]');
                const departmentDisplay = document.querySelector('[data-department-display]');
                const departmentInput = document.querySelector('[data-department-input]');
                const positionDisplay = document.querySelector('[data-position-display]');
                const positionInput = document.querySelector('[data-position-input]');

                if (!employeeSelect || !departmentInput || !positionInput) return;

                function syncEmployeeInfo() {
                    const option = employeeSelect.selectedOptions[0];
                    const hasEmployee = !!(option && option.value);
                    const deptId = hasEmployee ? (option.dataset.departmentId || '') : '';
                    const posId = hasEmployee ? (option.dataset.positionId || '') : '';

                    departmentInput.value = deptId;
                    positionInput.value = posId;

                    departmentDisplay.value = deptId
                        ? (option.dataset.departmentName || '')
                        : (hasEmployee ? 'Nhân viên chưa có phòng ban trong hồ sơ' : '');

                    positionDisplay.value = posId
                        ? (option.dataset.positionName || '')
                        : (hasEmployee ? 'Nhân viên chưa có chức vụ trong hồ sơ' : '');
                }

                employeeSelect.addEventListener('change', syncEmployeeInfo);
                syncEmployeeInfo();
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.querySelector('[data-contract-type-select]');
            const startInput = document.querySelector('[data-start-date]');
            const endInput = document.querySelector('[data-end-date]');
            const manualToggle = document.querySelector('[data-end-date-manual]');
            const hint = document.querySelector('[data-end-date-hint]');

            if (!typeSelect || !startInput || !endInput) return;

            function toDateInput(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            // Cộng tháng không tràn sang tháng kế: 31/01 + 1 tháng = 28/02.
            function addMonths(date, months) {
                const result = new Date(date.getTime());
                const day = result.getDate();
                result.setDate(1);
                result.setMonth(result.getMonth() + months);
                const lastDay = new Date(result.getFullYear(), result.getMonth() + 1, 0).getDate();
                result.setDate(Math.min(day, lastDay));
                return result;
            }

            function addDays(date, days) {
                const result = new Date(date.getTime());
                result.setDate(result.getDate() + days);
                return result;
            }

            function computeEndDate() {
                const option = typeSelect.selectedOptions[0];
                if (!option || !option.value || !startInput.value) {
                    return { value: '', note: 'Chọn loại hợp đồng và ngày bắt đầu để tự tính.' };
                }

                if (option.dataset.indefinite === '1') {
                    return { value: '', note: 'Hợp đồng không xác định thời hạn: không có ngày kết thúc.', locked: true };
                }

                const months = parseInt(option.dataset.months || '0', 10);
                if (!months) {
                    return { value: '', note: 'Loại hợp đồng chưa khai báo thời hạn, vui lòng chỉnh tay.' };
                }

                const start = new Date(`${startInput.value}T00:00:00`);
                if (isNaN(start.getTime())) {
                    return { value: '', note: 'Ngày bắt đầu không hợp lệ.' };
                }

                let end = addDays(addMonths(start, months), -1);

                const maxDays = parseInt(option.dataset.maxDays || '0', 10);
                if (maxDays) {
                    const cap = addDays(start, maxDays);
                    if (end > cap) end = cap;
                }

                const maxEndMonths = parseInt(option.dataset.maxEndMonths || '0', 10);
                if (maxEndMonths) {
                    let cap = addMonths(start, maxEndMonths);
                    if (option.dataset.maxEndExclusive === '1') cap = addDays(cap, -1);
                    if (end > cap) end = cap;
                }

                return { value: toDateInput(end), note: `Tự tính theo thời hạn ${months} tháng của loại hợp đồng.` };
            }

            function applyEndDate(force) {
                const manual = manualToggle ? manualToggle.checked : false;
                const result = computeEndDate();

                if (hint) {
                    hint.textContent = manual ? 'Đang chỉnh tay, hệ thống không tự tính ngày kết thúc.' : result.note;
                }

                if (result.locked) {
                    endInput.value = '';
                    endInput.readOnly = true;
                    endInput.classList.add('bg-slate-100', 'text-slate-600');
                    if (manualToggle) {
                        manualToggle.checked = false;
                        manualToggle.disabled = true;
                    }
                    return;
                }

                if (manualToggle) manualToggle.disabled = false;

                if (manual) {
                    endInput.readOnly = false;
                    endInput.classList.remove('bg-slate-100', 'text-slate-600');
                    return;
                }

                endInput.readOnly = true;
                endInput.classList.add('bg-slate-100', 'text-slate-600');

                if (force || !endInput.value) {
                    endInput.value = result.value;
                }
            }

            typeSelect.addEventListener('change', function () { applyEndDate(true); });
            startInput.addEventListener('change', function () { applyEndDate(true); });
            if (manualToggle) {
                manualToggle.addEventListener('change', function () { applyEndDate(!this.checked); });
            }

            applyEndDate(false);
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moneyInputs = document.querySelectorAll('.money-input');

            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            moneyInputs.forEach(function (input) {
                input.value = formatMoney(input.value);

                input.addEventListener('input', function () {
                    this.value = formatMoney(this.value);
                });

                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        input.value = (input.value || '').replace(/\D/g, '');
                    });
                }
            });
        });
    </script>
@endpush
