@php
    use App\Models\OvertimeRequest;
    use App\Support\TimeInput;
@endphp

<x-admin-layout title="Danh sách đơn tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Danh sách đơn tăng ca</h4>
                <p class="text-muted mb-0">Quản lý đơn tăng ca của nhân viên — đổi trạng thái trực tiếp tại bảng.</p>
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
                                <td>{{ TimeInput::forInput($item->start_time) }}</td>
                                <td>{{ TimeInput::forInput($item->end_time) }}</td>
                                <td>{{ $item->total_hours }}</td>
                                <td style="min-width: 180px;">
                                    <form method="POST"
                                          action="{{ route('admin.overtime-requests.status', $item) }}"
                                          class="overtime-status-form"
                                          data-current="{{ $item->status }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="reject_reason" value="">
                                        <select name="status"
                                                class="form-select form-select-sm overtime-status-select"
                                                aria-label="Trạng thái đơn tăng ca">
                                            @foreach (OvertimeRequest::STATUS_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        @if ($item->isPending())
                                            <form method="POST" action="{{ route('admin.overtime-requests.approve', $item) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Duyệt đơn này?')">Duyệt</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.overtime-requests.show', $item) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                                        <a href="{{ route('admin.overtime-requests.edit', $item) }}" class="btn btn-sm btn-outline-warning">Sửa</a>
                                        <form action="{{ route('admin.overtime-requests.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa đơn này?')">Xóa</button>
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

    @push('scripts')
    <script>
        document.querySelectorAll('.overtime-status-select').forEach(function (select) {
            select.addEventListener('change', function () {
                const form = this.closest('.overtime-status-form');
                const current = form.dataset.current;
                const next = this.value;

                if (next === current) {
                    return;
                }

                if (next === 'rejected') {
                    const reason = prompt('Nhập lý do từ chối:');
                    if (!reason || !reason.trim()) {
                        this.value = current;
                        return;
                    }
                    form.querySelector('[name="reject_reason"]').value = reason.trim();
                } else {
                    form.querySelector('[name="reject_reason"]').value = '';
                }

                if (!confirm('Cập nhật trạng thái đơn tăng ca này?')) {
                    this.value = current;
                    return;
                }

                form.submit();
            });
        });
    </script>
    @endpush
</x-admin-layout>
