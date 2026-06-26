<x-admin-layout title="Chi tiết hợp đồng">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Chi tiết hợp đồng</h4>
            <small class="text-muted">Mã: {{ $contract->contract_code }}</small>
        </div>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Danh sách</a>
            @if($contract->isEditable())
                <a class="btn btn-outline-primary" href="{{ route('admin.contracts.edit', $contract) }}">Sửa</a>
                <a class="btn btn-outline-success" href="{{ route('admin.contracts.extend.form', $contract) }}">Gia hạn</a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nhân viên</div>
                            <div class="fw-semibold">{{ $contract->employee->full_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Phòng ban</div>
                            <div class="fw-semibold">{{ $contract->department->department_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Chức vụ</div>
                            <div class="fw-semibold">{{ $contract->position->position_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Loại hợp đồng</div>
                            <div class="fw-semibold">{{ $contract->contractType->contract_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Trạng thái</div>
                            <span class="{{ $contract->status_badge_class }}">{{ $contract->status_label }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Người tạo</div>
                            <div class="fw-semibold">{{ $contract->creator->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Ngày bắt đầu</div>
                            <div class="fw-semibold">{{ optional($contract->start_date)->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Ngày kết thúc</div>
                            <div class="fw-semibold">{{ optional($contract->end_date)->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Ngày ký</div>
                            <div class="fw-semibold">{{ optional($contract->signed_date)->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Lương</div>
                            <div class="fw-semibold">{{ number_format($contract->salary, 0, ',', '.') }}₫</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Phụ cấp</div>
                            <div class="fw-semibold">{{ number_format($contract->allowance, 0, ',', '.') }}₫</div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-muted small">Mô tả</div>
                            <div>{{ $contract->description ?? '—' }}</div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-muted small">Ghi chú</div>
                            <div>{{ $contract->note ?? '—' }}</div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-muted small">File hợp đồng</div>
                            @if($contract->file_path)
                                <a class="btn btn-sm btn-outline-primary" href="{{ Storage::url($contract->file_path) }}" target="_blank">Tải xuống / Xem</a>
                            @else
                                <span class="text-muted">Chưa có tệp đính kèm</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Lịch sử hợp đồng của nhân viên</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Trạng thái</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($history as $item)
                            <tr class="{{ $item->id === $contract->id ? 'table-primary' : '' }}">
                                <td>{{ $item->contract_code }}</td>
                                <td>{{ optional($item->start_date)->format('d/m/Y') }}</td>
                                <td>{{ optional($item->end_date)->format('d/m/Y') }}</td>
                                <td><span class="{{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Chưa có lịch sử.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Hành động nhanh</div>
                <div class="card-body d-grid gap-2">
                    @if($contract->isEditable())
                        <a class="btn btn-warning" href="{{ route('admin.contracts.extend.form', $contract) }}"><i class="bi bi-arrow-repeat"></i> Gia hạn</a>
                        <form method="POST" action="{{ route('admin.contracts.cancel', $contract) }}" onsubmit="return confirm('Hủy hợp đồng này?')">
                            @csrf
                            <button class="btn btn-danger" type="submit"><i class="bi bi-x-circle"></i> Hủy hợp đồng</button>
                        </form>
                    @else
                        <span class="text-muted small">Hợp đồng không ở trạng thái cho phép sửa/hủy.</span>
                    @endif
                    @if($contract->isDeletable())
                        <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" onsubmit="return confirm('Chuyển vào thùng rác?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-trash"></i> Xóa mềm</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
