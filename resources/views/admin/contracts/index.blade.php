<x-admin-layout title="Quản lý hợp đồng">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Quản lý hợp đồng</h4>
            <small class="text-muted">Lọc, tìm kiếm, gia hạn, hủy và quản lý thùng rác.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm hợp đồng
            </a>
            <a href="{{ route('admin.contracts.trashed') }}" class="btn btn-outline-secondary">
                <i class="bi bi-trash"></i> Thùng rác
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('admin.contracts.index') }}">
                <div class="col-md-3">
                    <label class="form-label">Mã HĐ / Nhân viên</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="HD-2026-0001 / Tên">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Loại HĐ</label>
                    <select name="contract_type_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($contractTypes as $type)
                            <option value="{{ $type->id }}" @selected(($filters['contract_type_id'] ?? '') == $type->id)>{{ $type->contract_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nhân viên</label>
                    <select name="employee_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Phòng ban</label>
                    <select name="department_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(($filters['department_id'] ?? '') == $dept->id)>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Chức vụ</label>
                    <select name="position_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(($filters['position_id'] ?? '') == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="start_from" value="{{ $filters['start_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="start_to" value="{{ $filters['start_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-success" type="submit"><i class="bi bi-search"></i> Tìm</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Xóa bộ lọc</a>
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
                    <th>Mã HĐ</th>
                    <th>Nhân viên</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Loại HĐ</th>
                    <th>Lương</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contracts as $index => $contract)
                    <tr>
                        <td>{{ $contracts->firstItem() + $index }}</td>
                        <td>{{ $contract->contract_code }}</td>
                        <td>{{ $contract->employee->full_name ?? 'N/A' }}</td>
                        <td>{{ $contract->department->department_name ?? '—' }}</td>
                        <td>{{ $contract->position->position_name ?? '—' }}</td>
                        <td>{{ $contract->contractType->contract_name ?? '—' }}</td>
                        <td>{{ number_format($contract->salary, 0, ',', '.') }}₫</td>
                        <td>{{ optional($contract->start_date)->format('d/m/Y') }}</td>
                        <td>{{ optional($contract->end_date)->format('d/m/Y') }}</td>
                        <td><span class="{{ $contract->status_badge_class }}">{{ $contract->status_label }}</span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a class="btn btn-outline-primary" href="{{ route('admin.contracts.show', $contract) }}" title="Xem"><i class="bi bi-eye"></i></a>
                                @if($contract->isEditable())
                                    <a class="btn btn-outline-warning" href="{{ route('admin.contracts.edit', $contract) }}" title="Sửa"><i class="bi bi-pencil"></i></a>
                                    <a class="btn btn-outline-success" href="{{ route('admin.contracts.extend.form', $contract) }}" title="Gia hạn"><i class="bi bi-arrow-repeat"></i></a>
                                    <form method="POST" action="{{ route('admin.contracts.cancel', $contract) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-danger" onclick="return confirm('Hủy hợp đồng này?')" title="Hủy"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                @endif
                                @if($contract->isDeletable())
                                    <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-secondary" onclick="return confirm('Chuyển hợp đồng vào thùng rác?')" title="Xóa mềm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Không có hợp đồng phù hợp.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())
            <div class="card-footer">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
