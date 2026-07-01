<x-admin-layout title="Tạo đơn tăng ca">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Tạo đơn tăng ca</h4>
                <p class="text-muted mb-0">Tạo đơn cho từng nhân viên, theo phòng ban hoặc toàn công ty.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.index') }}" class="btn btn-outline-secondary">
                Quay lại danh sách
            </a>
        </div>

        <x-flash-messages />

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('admin.overtime-requests.store') }}" method="POST" class="row g-3">
                    @csrf

                    @include('overtime-requests.partials.assignment-scope-fields', [
                        'employees' => $employees,
                        'departments' => $departments,
                        'companyEmployeeCount' => $companyEmployeeCount,
                    ])

                    @include('overtime-requests.partials.form-fields', [
                        'employees' => $employees,
                        'employeeRequired' => false,
                        'showTotalHours' => false,
                        'hideEmployeeSelect' => true,
                    ])

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <button type="reset" class="btn btn-light border">Làm mới</button>
                        <button type="submit" class="btn btn-primary">Tạo đơn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
