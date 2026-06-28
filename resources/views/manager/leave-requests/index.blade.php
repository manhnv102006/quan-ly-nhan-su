<x-admin-layout title="Duyệt nghỉ phép">
    <x-flash-messages />

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Đơn nghỉ phép cấp dưới</h4>
            <small class="text-muted">Thống kê và danh sách chỉ tính trên nhân viên thuộc quyền quản lý.</small>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Chờ duyệt</div>
                    <div class="h3 mb-0 text-warning">{{ number_format($stats['pending']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đã duyệt</div>
                    <div class="h3 mb-0 text-success">{{ number_format($stats['approved']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Từ chối</div>
                    <div class="h3 mb-0 text-danger">{{ number_format($stats['rejected']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('manager.leave-requests.index') }}">
                <div class="col-md-3">
                    <label class="form-label">Tìm kiếm nhân viên</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                           placeholder="Tên hoặc mã nhân viên">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Loại nghỉ</label>
                    <select name="leave_type" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(\App\Models\LeaveRequest::LEAVE_TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['leave_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Chờ duyệt</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Đã duyệt</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nghỉ từ ngày</label>
                    <input type="date" name="start_from" value="{{ $filters['start_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nghỉ đến ngày</label>
                    <input type="date" name="start_to" value="{{ $filters['start_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button class="btn btn-success w-100" type="submit" title="Lọc">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('manager.leave-requests.index') }}">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mã NV</th>
                        <th>Nhân viên</th>
                        <th>Loại</th>
                        <th>Từ ngày</th>
                        <th>Đến ngày</th>
                        <th>Số ngày</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $index => $item)
                        <tr>
                            <td>{{ $leaveRequests->firstItem() + $index }}</td>
                            <td>{{ $item->employee->employee_code ?? '—' }}</td>
                            <td>{{ $item->employee->full_name ?? '—' }}</td>
                            <td>{{ \App\Models\LeaveRequest::LEAVE_TYPE_LABELS[$item->leave_type] ?? $item->leave_type }}</td>
                            <td>{{ optional($item->start_date)->format('d/m/Y') }}</td>
                            <td>{{ optional($item->end_date)->format('d/m/Y') }}</td>
                            <td>{{ $item->total_days }}</td>
                            <td><x-status-badge :model="$item" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.leave-requests.show', $item) }}">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Không có đơn nghỉ phép phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaveRequests->hasPages())
            <div class="card-footer">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
