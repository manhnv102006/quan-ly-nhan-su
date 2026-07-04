@php
    $isEdit = isset($contract);
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
                            data-position-id="{{ $employee->position_id }}"
                            @selected(old('employee_id') == $employee->id)>
                        {{ $employee->full_name }} ({{ $employee->employee_code }})
                    </option>
                @endforeach
            </select>
        @endif
        @error('employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contract_type_id" class="admin-label">Loại hợp đồng *</label>
        <select id="contract_type_id" name="contract_type_id" class="admin-field" required>
            <option value="">— Chọn loại —</option>
            @foreach($contractTypes as $type)
                <option value="{{ $type->id }}" @selected(old('contract_type_id', $isEdit ? $contract->contract_type_id : null) == $type->id)>
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
        <select id="department_id" name="department_id" class="admin-field" required data-department-select>
            <option value="">— Chọn phòng ban —</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id', $isEdit ? $contract->department_id : null) == $dept->id)>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select>
        @error('department_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="position_id" class="admin-label">Chức vụ *</label>
        <select id="position_id" name="position_id" class="admin-field" required data-position-select>
            <option value="">— Chọn chức vụ —</option>
            @foreach($positions as $pos)
                <option value="{{ $pos->id }}" @selected(old('position_id', $isEdit ? $contract->position_id : null) == $pos->id)>
                    {{ $pos->position_name }}
                </option>
            @endforeach
        </select>
        @error('position_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="salary" class="admin-label">Lương cơ bản *</label>
        <input type="number" id="salary" name="salary" class="admin-field" min="1"
               value="{{ old('salary', $isEdit ? $contract->salary : '') }}" required>
        @error('salary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowance_meal" class="admin-label">Phụ cấp ăn trưa</label>
        <input type="number" id="allowance_meal" name="allowance_meal" class="admin-field sub-allowance" min="0"
               value="{{ old('allowance_meal', $isEdit ? (int)$contract->allowance_meal : 0) }}">
        @error('allowance_meal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowance_phone" class="admin-label">Phụ cấp điện thoại</label>
        <input type="number" id="allowance_phone" name="allowance_phone" class="admin-field sub-allowance" min="0"
               value="{{ old('allowance_phone', $isEdit ? (int)$contract->allowance_phone : 0) }}">
        @error('allowance_phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowance_fuel" class="admin-label">Phụ cấp xăng xe</label>
        <input type="number" id="allowance_fuel" name="allowance_fuel" class="admin-field sub-allowance" min="0"
               value="{{ old('allowance_fuel', $isEdit ? (int)$contract->allowance_fuel : 0) }}">
        @error('allowance_fuel')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowance_position" class="admin-label">Phụ cấp chức vụ</label>
        <input type="number" id="allowance_position" name="allowance_position" class="admin-field sub-allowance" min="0"
               value="{{ old('allowance_position', $isEdit ? (int)$contract->allowance_position : 0) }}">
        @error('allowance_position')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="allowance" class="admin-label">Tổng phụ cấp hàng tháng (Tự động tính)</label>
        <input type="number" id="allowance" name="allowance" class="admin-field bg-slate-50 cursor-not-allowed" min="0"
               value="{{ old('allowance', $isEdit ? (int)$contract->allowance : 0) }}" readonly>
        @error('allowance')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="start_date" class="admin-label">Ngày bắt đầu *</label>
        <input type="date" id="start_date" name="start_date" class="admin-field"
               value="{{ old('start_date', $isEdit ? $contract->start_date?->format('Y-m-d') : '') }}" required>
        @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="end_date" class="admin-label">Ngày kết thúc *</label>
        <input type="date" id="end_date" name="end_date" class="admin-field"
               value="{{ old('end_date', $isEdit ? $contract->end_date?->format('Y-m-d') : '') }}" required>
        @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="signed_date" class="admin-label">Ngày ký</label>
        <input type="date" id="signed_date" name="signed_date" class="admin-field"
               value="{{ old('signed_date', $isEdit ? $contract->signed_date?->format('Y-m-d') : '') }}">
        @error('signed_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contract_file" class="admin-label">File hợp đồng</label>
        <input type="file" id="contract_file" name="contract_file" class="admin-field"
               accept=".pdf,.doc,.docx">
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
        <label for="description" class="admin-label">Mô tả</label>
        <textarea id="description" name="description" rows="2" class="admin-field" placeholder="Mô tả ngắn">{{ old('description', $isEdit ? $contract->description : '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="note" class="admin-label">Ghi chú nội bộ</label>
        <textarea id="note" name="note" rows="2" class="admin-field" placeholder="Ghi chú nội bộ">{{ old('note', $isEdit ? $contract->note : '') }}</textarea>
        @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

@if(! $isEdit)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const employeeSelect = document.querySelector('[data-employee-select]');
                const departmentSelect = document.querySelector('[data-department-select]');
                const positionSelect = document.querySelector('[data-position-select]');

                if (!employeeSelect || !departmentSelect || !positionSelect) return;

                employeeSelect.addEventListener('change', function () {
                    const option = this.selectedOptions[0];
                    if (!option || !option.value) return;

                    const deptId = option.dataset.departmentId;
                    const posId = option.dataset.positionId;

                    if (deptId) departmentSelect.value = deptId;
                    if (posId) positionSelect.value = posId;
                });
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subAllowances = document.querySelectorAll('.sub-allowance');
            const totalAllowanceInput = document.getElementById('allowance');

            if (subAllowances.length && totalAllowanceInput) {
                function calculateTotalAllowance() {
                    let total = 0;
                    subAllowances.forEach(input => {
                        const val = parseFloat(input.value) || 0;
                        total += val;
                    });
                    totalAllowanceInput.value = total;
                }

                subAllowances.forEach(input => {
                    input.addEventListener('input', calculateTotalAllowance);
                    input.addEventListener('change', calculateTotalAllowance);
                });
            }
        });
    </script>
@endpush
