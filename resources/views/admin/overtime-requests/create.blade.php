<x-admin-layout title="Tạo đơn tăng ca">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Tạo đơn tăng ca</h4>
                <p class="text-muted mb-0">Điền thông tin tăng ca của nhân viên.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.index') }}" class="btn btn-outline-secondary">
                Quay lại danh sách
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.overtime-requests.store') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label for="work_date" class="form-label fw-semibold">Ngày</label>
                        <input type="date" id="work_date" name="work_date" class="form-control @error('work_date') is-invalid @enderror" value="{{ old('work_date') }}" required>
                        @error('work_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="start_time" class="form-label fw-semibold">Giờ bắt đầu</label>
                        <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label fw-semibold">Giờ kết thúc</label>
                        <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="reason" class="form-label fw-semibold">Lý do</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Nhập lý do tăng ca..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('employee_id')
                        <div class="col-12">
                            <div class="alert alert-danger mb-0">{{ $message }}</div>
                        </div>
                    @enderror

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="reset" class="btn btn-light border">Làm mới</button>
                        <button type="submit" class="btn btn-primary">
                            Tạo đơn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
