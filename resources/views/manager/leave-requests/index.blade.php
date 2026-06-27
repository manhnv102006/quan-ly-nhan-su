<x-admin-layout title="Duyệt nghỉ phép">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Đơn nghỉ phép cấp dưới</h4>
            <small class="text-muted">Quản lý chỉ xem và duyệt nhân viên thuộc quyền.</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('manager.leave-requests.index') }}">
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Chờ duyệt</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Đã duyệt</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Đã từ chối</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-success" type="submit"><i class="bi bi-search"></i> Lọc</button>
                    <a class="btn btn-outline-secondary" href="{{ route('manager.leave-requests.index') }}">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
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
                            <td>{{ $item->employee->full_name ?? '—' }}</td>
                            <td>{{ ucfirst($item->leave_type) }}</td>
                            <td>{{ optional($item->start_date)->format('d/m/Y') }}</td>
                            <td>{{ optional($item->end_date)->format('d/m/Y') }}</td>
                            <td>{{ $item->total_days }}</td>
                            <td>
                                @php
                                    $badge = [
                                        'pending' => 'badge text-bg-warning',
                                        'approved' => 'badge text-bg-success',
                                        'rejected' => 'badge text-bg-danger',
                                    ][$item->status] ?? 'badge text-bg-secondary';
                                    $label = [
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Đã từ chối',
                                    ][$item->status] ?? $item->status;
                                @endphp
                                <span class="{{ $badge }}">{{ $label }}</span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.leave-requests.show', $item) }}">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Không có đơn nghỉ phép.</td>
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
