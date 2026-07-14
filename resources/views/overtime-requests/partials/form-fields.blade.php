@php
    use App\Support\TimeInput;
    $model = $overtimeRequest ?? null;
    $employeeRequired = $employeeRequired ?? false;
    $hideEmployeeSelect = $hideEmployeeSelect ?? false;
@endphp

@if (! $hideEmployeeSelect)
<div class="col-md-6">
    <label for="employee_id" class="form-label fw-semibold">Nhân viên</label>
    <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" @if($employeeRequired) required @endif>
        <option value="">-- Chọn nhân viên --</option>
        @foreach($employees as $employee)
            <option value="{{ $employee->id }}" @selected(old('employee_id', $model?->employee_id) == $employee->id)>
                {{ $employee->full_name }} ({{ $employee->employee_code }})
            </option>
        @endforeach
    </select>
    @error('employee_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

<div class="col-md-4">
    <label for="work_date" class="form-label fw-semibold">Ngày</label>
    <input type="date" id="work_date" name="work_date" class="form-control @error('work_date') is-invalid @enderror"
           value="{{ old('work_date', optional($model?->work_date)->format('Y-m-d')) }}" required>
    @error('work_date')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="start_time" class="form-label fw-semibold">Giờ bắt đầu</label>
    <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
           value="{{ old('start_time', TimeInput::forInput($model?->start_time)) }}" required>
    @error('start_time')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="end_time" class="form-label fw-semibold">Giờ kết thúc</label>
    <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
           value="{{ old('end_time', TimeInput::forInput($model?->end_time)) }}" required>
    @error('end_time')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if($showTotalHours ?? false)
    <div class="col-md-4">
        <label for="total_hours" class="form-label fw-semibold">Tổng giờ</label>
        <input type="number" step="0.25" min="0" id="total_hours" name="total_hours" class="form-control @error('total_hours') is-invalid @enderror"
               value="{{ old('total_hours', $model?->total_hours) }}">
        @error('total_hours')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif

<div class="col-md-4">
    <label for="rate_multiplier" class="form-label fw-semibold">Loại ngày tăng ca</label>
    <select id="rate_multiplier" name="rate_multiplier" class="form-select @error('rate_multiplier') is-invalid @enderror" required>
        <option value="1.5" @selected(old('rate_multiplier', $model?->rate_multiplier) == '1.5')>Ngày thường (x1.5)</option>
        <option value="2.0" @selected(old('rate_multiplier', $model?->rate_multiplier) == '2.0')>Ngày nghỉ cuối tuần (x2.0)</option>
        <option value="3.0" @selected(old('rate_multiplier', $model?->rate_multiplier) == '3.0')>Ngày Lễ, Tết (x3.0)</option>
    </select>
    @error('rate_multiplier')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


@if($showStatus ?? false)
    <div class="col-md-4">
        <label for="status" class="form-label fw-semibold">Trạng thái</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(\App\Models\OvertimeRequest::STATUS_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $model?->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12" id="reject-reason-wrap">
        <label for="reject_reason" class="form-label fw-semibold">Lý do từ chối</label>
        <textarea id="reject_reason" name="reject_reason" rows="3" class="form-control @error('reject_reason') is-invalid @enderror"
                  placeholder="Bắt buộc khi chọn trạng thái Từ chối">{{ old('reject_reason', $model?->reject_reason) }}</textarea>
        @error('reject_reason')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif

<div class="col-12">
    <label for="reason" class="form-label fw-semibold">Lý do</label>
    <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
              placeholder="Nhập lý do tăng ca..." required>{{ old('reason', $model?->reason) }}</textarea>
    @error('reason')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
