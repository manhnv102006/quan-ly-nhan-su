<x-admin-layout title="Danh sách đơn tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Danh sách đơn tăng ca</h4>
                <p class="text-muted mb-0">Quản lý đơn tăng ca của nhân viên.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tạo đơn
            </a>
        </div>

        <x-flash-messages />

        <x-request-stats-cards :stats="$stats" :completed="$stats['completed']" />

        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày tăng ca</th>
                            <th>Giờ bắt đầu</th>
                            <th>Giờ kết thúc</th>
                            <th>Tổng giờ</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimeRequests as $item)
                            <tr>
                                <td>{{ $item->employee?->full_name ?? '—' }}</td>
                                <td>{{ optional($item->work_date)->format('d/m/Y') }}</td>
                                <td>{{ $item->start_time }}</td>
                                <td>{{ $item->end_time }}</td>
                                <td>{{ $item->total_hours }}</td>
                                <td>
                                    <x-status-badge :model="$item" />
                                </td>
                                <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.overtime-requests.show', $item) }}" class="btn btn-outline-primary">Xem</a>
                                        <a href="{{ route('admin.overtime-requests.edit', $item) }}" class="btn btn-outline-warning">Sửa</a>
                                        <form action="{{ route('admin.overtime-requests.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa đơn này?')">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Chưa có đơn tăng ca nào.</td>
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