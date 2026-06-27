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
                <form action="#" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label for="work_date" class="form-label fw-semibold">Ngày</label>
                        <input type="date" id="work_date" name="work_date" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label for="start_time" class="form-label fw-semibold">Giờ bắt đầu</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label fw-semibold">Giờ kết thúc</label>
                        <input type="time" id="end_time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label for="reason" class="form-label fw-semibold">Lý do</label>
                        <textarea id="reason" name="reason" rows="4" class="form-control" placeholder="Nhập lý do tăng ca..." required></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="reset" class="btn btn-light border">Làm mới</button>
                        <button type="button" class="btn btn-primary">
                            Tạo đơn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
