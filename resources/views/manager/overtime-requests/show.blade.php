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
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
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
                        <div>
                            @php
                                $statusClass = match($overtimeRequest->status) {
                                    \App\Models\OvertimeRequest::STATUS_PENDING => 'text-bg-warning',
                                    \App\Models\OvertimeRequest::STATUS_APPROVED => 'text-bg-success',
                                    \App\Models\OvertimeRequest::STATUS_REJECTED => 'text-bg-danger',
                                    \App\Models\OvertimeRequest::STATUS_COMPLETED => 'text-bg-primary',
                                    default => 'text-bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($overtimeRequest->status) }}</span>
                        </div>
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
                        <form id="approve-form" action="{{ route('manager.overtime-requests.approve', $overtimeRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">Phê duyệt</button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Từ chối</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING)
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="reject-form" action="{{ route('manager.overtime-requests.reject', $overtimeRequest) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Từ chối đơn tăng ca</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="reject_reason" class="form-label">Lý do từ chối</label>
                            <textarea id="reject_reason" name="reject_reason" rows="4" class="form-control @error('reject_reason') is-invalid @enderror" required>{{ old('reject_reason') }}</textarea>
                            @error('reject_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if($overtimeRequest->status === \App\Models\OvertimeRequest::STATUS_PENDING)
        <script>
            const approveForm = document.getElementById('approve-form');
            const rejectForm = document.getElementById('reject-form');

            if (approveForm) {
                approveForm.addEventListener('submit', function (event) {
                    if (approveForm.dataset.confirmed === '1') return;

                    event.preventDefault();
                    Swal.fire({
                        title: 'Xác nhận phê duyệt?',
                        text: 'Bạn có chắc muốn phê duyệt đơn tăng ca này?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Phê duyệt',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#198754'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            approveForm.dataset.confirmed = '1';
                            approveForm.submit();
                        }
                    });
                });
            }

            if (rejectForm) {
                rejectForm.addEventListener('submit', function (event) {
                    if (rejectForm.dataset.confirmed === '1') return;

                    const reason = document.getElementById('reject_reason')?.value?.trim() || '';
                    if (!reason) {
                        return;
                    }

                    event.preventDefault();
                    Swal.fire({
                        title: 'Xác nhận từ chối?',
                        text: 'Bạn có chắc muốn từ chối đơn tăng ca này?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Từ chối',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#dc3545'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            rejectForm.dataset.confirmed = '1';
                            rejectForm.submit();
                        }
                    });
                });
            }
        </script>
    @endif
</x-admin-layout>
