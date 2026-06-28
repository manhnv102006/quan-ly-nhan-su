<x-admin-layout title="Chi tiết đơn nghỉ phép">
    <x-flash-messages />

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Đơn nghỉ phép</h4>
            <small class="text-muted">Nhân viên: {{ $leaveRequest->employee->full_name ?? '—' }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manager.leave-requests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            @can('approve', $leaveRequest)
                @if($leaveRequest->isPending())
                    <form method="POST" action="{{ route('manager.leave-requests.approve', $leaveRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success" type="submit" onclick="return confirm('Duyệt đơn này?')">
                            <i class="bi bi-check2-circle"></i> Duyệt
                        </button>
                    </form>
                @endif
            @endcan
            @can('reject', $leaveRequest)
                @if($leaveRequest->isPending())
                    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Từ chối
                    </button>
                @endif
            @endcan
        </div>
    </div>

    @include('leave-requests.partials.history-table', ['leaveRequest' => $leaveRequest])

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Nhân viên</div>
                    <div class="fw-semibold">{{ $leaveRequest->employee->full_name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Phòng ban</div>
                    <div class="fw-semibold">{{ $leaveRequest->employee->department->department_name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Chức vụ</div>
                    <div class="fw-semibold">{{ $leaveRequest->employee->position->position_name ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Loại nghỉ</div>
                    <div class="fw-semibold">{{ \App\Models\LeaveRequest::LEAVE_TYPE_LABELS[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Từ ngày</div>
                    <div class="fw-semibold">{{ optional($leaveRequest->start_date)->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Đến ngày</div>
                    <div class="fw-semibold">{{ optional($leaveRequest->end_date)->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Số ngày</div>
                    <div class="fw-semibold">{{ $leaveRequest->total_days }}</div>
                </div>
                <div class="col-md-12">
                    <div class="text-muted small">Lý do</div>
                    <div class="fw-semibold">{{ $leaveRequest->reason ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Trạng thái</div>
                    <x-status-badge :model="$leaveRequest" />
                </div>
            </div>
        </div>
    </div>

    @if(in_array($leaveRequest->status, [\App\Models\LeaveRequest::STATUS_APPROVED, \App\Models\LeaveRequest::STATUS_REJECTED], true))
        <div class="card mb-3">
            <div class="card-header fw-semibold">Thông tin phê duyệt</div>
            <div class="card-body">
                <div class="row g-3">
                    @include('leave-requests.partials.approval-info', ['leaveRequest' => $leaveRequest])
                </div>
            </div>
        </div>
    @endif

    @can('reject', $leaveRequest)
    @if($leaveRequest->isPending())
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('manager.leave-requests.reject', $leaveRequest) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Từ chối đơn nghỉ phép</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea name="reject_reason" class="form-control @error('reject_reason') is-invalid @enderror" rows="3" required minlength="1">{{ old('reject_reason') }}</textarea>
                                @error('reject_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @endcan

    @if($leaveRequest->isPending() && $errors->has('reject_reason'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('rejectModal');
                if (modal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endif
</x-admin-layout>
