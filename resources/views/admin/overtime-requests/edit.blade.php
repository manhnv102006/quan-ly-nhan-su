<x-admin-layout title="Sửa đơn tăng ca">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Sửa đơn tăng ca</h4>
                <p class="text-muted mb-0">Chỉnh sửa thông tin đơn tăng ca đang chờ xử lý.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.show', $overtimeRequest) }}" class="btn btn-outline-secondary">
                Quay lại chi tiết
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.overtime-requests.update', $overtimeRequest) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label for="employee_id" class="form-label fw-semibold">Nhân viên</label>
                        <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id', $overtimeRequest->employee_id) == $employee->id)>
                                    {{ $employee->full_name }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="work_date" class="form-label fw-semibold">Ngày</label>
                        <input type="date" id="work_date" name="work_date" class="form-control @error('work_date') is-invalid @enderror"
                               value="{{ old('work_date', optional($overtimeRequest->work_date)->format('Y-m-d')) }}" required>
                        @error('work_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="start_time" class="form-label fw-semibold">Giờ bắt đầu</label>
                        <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time', $overtimeRequest->start_time) }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label fw-semibold">Giờ kết thúc</label>
                        <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time', $overtimeRequest->end_time) }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="total_hours" class="form-label fw-semibold">Tổng giờ</label>
                        <input type="number" step="0.25" min="0" id="total_hours" name="total_hours" class="form-control @error('total_hours') is-invalid @enderror"
                               value="{{ old('total_hours', $overtimeRequest->total_hours) }}">
                        @error('total_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="reason" class="form-label fw-semibold">Lý do</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason', $overtimeRequest->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.overtime-requests.show', $overtimeRequest) }}" class="btn btn-light border">Hủy</a>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
