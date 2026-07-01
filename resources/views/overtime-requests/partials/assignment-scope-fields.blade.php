@php
    $scope = old('assignment_scope', 'employee');
@endphp

<div class="col-12">
    <div class="rounded-3 border bg-light p-3 p-md-4" x-data="{ scope: @js($scope) }">
        <p class="fw-semibold mb-3 mb-md-2">Phạm vi tạo đơn</p>

        <div class="d-flex flex-wrap gap-3 mb-3">
            <label class="form-check form-check-inline mb-0">
                <input type="radio" name="assignment_scope" value="employee" class="form-check-input" x-model="scope">
                <span class="form-check-label">Từng nhân viên</span>
            </label>
            <label class="form-check form-check-inline mb-0">
                <input type="radio" name="assignment_scope" value="department" class="form-check-input" x-model="scope">
                <span class="form-check-label">Theo phòng ban</span>
            </label>
            <label class="form-check form-check-inline mb-0">
                <input type="radio" name="assignment_scope" value="company" class="form-check-input" x-model="scope">
                <span class="form-check-label">Toàn công ty</span>
            </label>
        </div>

        @error('assignment_scope')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror

        <div x-show="scope === 'employee'" x-cloak>
            <label for="employee_id" class="form-label fw-semibold">Nhân viên</label>
            <select id="employee_id"
                    name="employee_id"
                    class="form-select @error('employee_id') is-invalid @enderror"
                    :disabled="scope !== 'employee'">
                <option value="">-- Chọn nhân viên --</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                        {{ $employee->full_name }} ({{ $employee->employee_code }})
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div x-show="scope === 'department'" x-cloak>
            <label for="department_id" class="form-label fw-semibold">Phòng ban</label>
            <select id="department_id"
                    name="department_id"
                    class="form-select @error('department_id') is-invalid @enderror"
                    :disabled="scope !== 'department'">
                <option value="">-- Chọn phòng ban --</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                        {{ $department->department_name }} ({{ $department->department_code }})
                        — {{ $department->active_employees_count }} nhân viên
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <p class="form-text mb-0">Tạo đơn tăng ca cho tất cả nhân viên đang hoạt động trong phòng ban đã chọn.</p>
        </div>

        <div x-show="scope === 'company'" x-cloak class="alert alert-primary mb-0 py-2">
            Tạo đơn tăng ca cho <strong>{{ number_format($companyEmployeeCount) }}</strong> nhân viên đang hoạt động toàn công ty.
        </div>
    </div>
</div>
