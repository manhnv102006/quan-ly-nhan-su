<x-admin-layout title="Chi tiết đơn tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn tăng ca</h4>
                <p class="text-muted mb-0">Thông tin chi tiết đơn tăng ca của nhân viên.</p>
            </div>
            <a href="{{ route('manager.overtime-requests.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Tên nhân viên</label>
                        <div class="fw-semibold">{{ $overtimeRequest->employee?->full_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1">Phòng ban</label>
                        <div class="fw-semibold">{{ $overtimeRequest->employee?->department?->department_name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Ngày</label>
                        <div class="fw-semibold">{{ optional($overtimeRequest->work_date)->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Giờ bắt đầu</label>
                        <div class="fw-semibold">{{ $overtimeRequest->start_time }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Giờ kết thúc</label>
                        <div class="fw-semibold">{{ $overtimeRequest->end_time }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Tổng giờ</label>
                        <div class="fw-semibold">{{ $overtimeRequest->total_hours }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Trạng thái</label>
                        <div><span class="badge text-bg-secondary">{{ $overtimeRequest->status }}</span></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1">Ngày duyệt</label>
                        <div class="fw-semibold">{{ optional($overtimeRequest->approved_at)->format('d/m/Y H:i') ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted mb-1">Lý do</label>
                        <div class="border rounded-3 p-3 bg-light">{{ $overtimeRequest->reason ?: '—' }}</div>
                    </div>
                </div>

                @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING)
                    <div class="d-flex gap-2 mt-4">
                        <form action="{{ route('manager.overtime-requests.approve', $overtimeRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">Phê duyệt</button>
                        </form>
                        <button type="button" class="btn btn-danger">Từ chối</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
