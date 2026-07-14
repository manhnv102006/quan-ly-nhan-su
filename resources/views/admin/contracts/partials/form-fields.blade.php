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
                <option value="{{ $type->id }}"
                        data-internship="{{ $type->isInternship() ? '1' : '0' }}"
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
                <li>Thử việc: có ngày kết thúc, tối đa 60 ngày.</li>
                <li>Xác định thời hạn: có ngày kết thúc, tối đa 36 tháng.</li>
                <li>Không xác định thời hạn: không nhập ngày kết thúc.</li>
                <li>Thời vụ: có ngày kết thúc, dưới 12 tháng.</li>
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
        <input type="date" id="start_date" name="start_date" class="admin-field"
               value="{{ old('start_date', $isEdit ? $contract->start_date?->format('Y-m-d') : '') }}" required>
        @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="end_date" class="admin-label">Ngày kết thúc</label>
        <input type="date" id="end_date" name="end_date" class="admin-field"
               value="{{ old('end_date', $isEdit ? $contract->end_date?->format('Y-m-d') : '') }}">
        <p class="mt-1 text-[11px] text-slate-400">Để trống nếu loại HĐ không xác định thời hạn.</p>
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

