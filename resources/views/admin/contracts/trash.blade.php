<x-admin-layout title="Hợp đồng đã xóa">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Thùng rác hợp đồng</h4>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Danh sách</a>
            <a class="btn btn-primary" href="{{ route('admin.contracts.create') }}">Thêm hợp đồng</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('admin.contracts.trashed') }}">
                <div class="col-md-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã HĐ hoặc tên nhân viên">
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button class="btn btn-success" type="submit">Tìm</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.trashed') }}">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Mã HĐ</th>
                    <th>Nhân viên</th>
                    <th>Loại</th>
                    <th>Ngày xóa</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contracts as $contract)
                    <tr>
                        <td>{{ $contract->contract_code }}</td>
                        <td>{{ $contract->employee->full_name ?? '—' }}</td>
                        <td>{{ $contract->contractType->contract_name ?? '—' }}</td>
                        <td>{{ $contract->deleted_at?->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-primary" href="{{ route('admin.contracts.show', $contract->id) }}">Xem</a>
                                <form method="POST" action="{{ route('admin.contracts.restore', $contract->id) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-success" onclick="return confirm('Khôi phục hợp đồng này?')">Khôi phục</button>
                                </form>
                                <form method="POST" action="{{ route('admin.contracts.forceDelete', $contract->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn hợp đồng này?')">Xóa vĩnh viễn</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Không có hợp đồng nào trong thùng rác.</td>
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
