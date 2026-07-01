<x-admin-layout title="Sửa đơn tăng ca">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Sửa đơn tăng ca</h4>
                <p class="text-muted mb-0">Chỉnh sửa thông tin và trạng thái đơn tăng ca.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.show', $overtimeRequest) }}" class="btn btn-outline-secondary">
                Quay lại chi tiết
            </a>
        </div>

        <x-flash-messages />

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.overtime-requests.update', $overtimeRequest) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')
                    @include('overtime-requests.partials.form-fields', [
                        'overtimeRequest' => $overtimeRequest,
                        'employees' => $employees,
                        'employeeRequired' => true,
                        'showTotalHours' => true,
                        'showStatus' => true,
                    ])

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.overtime-requests.show', $overtimeRequest) }}" class="btn btn-light border">Hủy</a>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
