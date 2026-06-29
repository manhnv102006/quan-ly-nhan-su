@php($navigation = \App\Support\ManagerNavigation::items())

<x-staff-layout
    title="Chi tiết đơn tăng ca"
    role="manager"
    :navigation="$navigation"
    :bootstrap="true"
>
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn tăng ca</h4>
                <p class="text-muted mb-0">Thông tin chi tiết đơn tăng ca của nhân viên.</p>
            </div>
            <a href="{{ route('manager.overtime-requests.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>

        <x-flash-messages />

        <div class="card">
            <div class="card-body">
                @include('overtime-requests.partials.detail-fields', [
                    'overtimeRequest' => $overtimeRequest,
                    'approverLabel' => 'Ngày duyệt',
                    'showApprovedAt' => true,
                ])

                @if($overtimeRequest->isPending())
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

        @include('overtime-requests.partials.history-table', ['overtimeRequest' => $overtimeRequest])
    </div>

    @if($overtimeRequest->isPending())
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
    @if($overtimeRequest->isPending())
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
</x-staff-layout>
