<x-admin-layout title="Chi tiết đơn nghỉ phép">
    <x-flash-messages />

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Đơn nghỉ phép</h4>
            <small class="text-muted">Nhân viên: {{ $leaveRequest->employee->full_name ?? '—' }}</small>
        </div>
        <div class="d-flex gap-2">
            @if($leaveRequest->isPending())
                <form method="POST" action="{{ route('manager.leave-requests.approve', $leaveRequest) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-success" type="submit" onclick="return confirm('Duyệt đơn này?')">
                        <i class="bi bi-check2-circle"></i> Duyệt
                    </button>
                </form>
                <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle"></i> Từ chối
                </button>
            @else
                <span class="badge bg-secondary">Đã xử lý</span>
            @endif
        </div>
    </div>

    @if($leaveRequest->histories->count())
        <div class="card mb-3">
            <div class="card-header fw-semibold">Lịch sử xử lý</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                            <th>Người thực hiện</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveRequest->histories->sortByDesc('created_at') as $history)
                            <tr>
                                <td>{{ optional($history->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $history->action === 'approved' ? 'Duyệt' : 'Từ chối' }}</td>
                                <td>{{ $history->actor->name ?? '—' }}</td>
                                <td>{{ $history->note ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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
                    <div class="fw-semibold text-capitalize">{{ $leaveRequest->leave_type }}</div>
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
                <div class="col-md-4">
                    <div class="text-muted small">Người duyệt</div>
                    <div class="fw-semibold">{{ $leaveRequest->approver->name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Thời gian duyệt</div>
                    <div class="fw-semibold">{{ optional($leaveRequest->approved_at)->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
                <div class="col-md-12">
                    <div class="text-muted small">Lý do từ chối</div>
                    <div class="fw-semibold">{{ $leaveRequest->reject_reason ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
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
                            <label class="form-label">Lý do từ chối</label>
                            <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
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
</x-admin-layout>
