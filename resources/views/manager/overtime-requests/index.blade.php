<x-admin-layout title="Quản lý tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Quản lý đơn tăng ca</h4>
                <p class="text-muted mb-0">Chỉ hiển thị đơn của nhân viên thuộc phòng ban do quản lý phụ trách.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.overtime-requests.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                            <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                            <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ngày</label>
                        <input type="date" name="work_date" class="form-control" value="{{ $filters['work_date'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhân viên</label>
                        <select name="employee_id" class="form-select">
                            <option value="">Tất cả nhân viên</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>
                                    {{ $employee->full_name }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                        <a href="{{ route('manager.overtime-requests.index') }}" class="btn btn-outline-secondary w-100">Bỏ lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày tăng ca</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Tổng giờ</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimeRequests as $item)
                            <tr>
                                <td>{{ $item->employee?->full_name }}</td>
                                <td>{{ optional($item->work_date)->format('d/m/Y') }}</td>
                                <td>{{ $item->start_time }}</td>
                                <td>{{ $item->end_time }}</td>
                                <td>{{ $item->total_hours }}</td>
                                <td><span class="badge text-bg-secondary">{{ $item->status }}</span></td>
                                <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Không có đơn tăng ca phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($overtimeRequests->hasPages())
                <div class="card-footer">
                    {{ $overtimeRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
