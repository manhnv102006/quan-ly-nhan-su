<x-admin-layout title="Quản lý chức vụ">
    <div class="admin-card p-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-2xl font-bold text-slate-800">Danh sách chức vụ</h2>
                <p class="text-sm text-slate-500">Quản lý chức vụ đang hoạt động và xóa mềm.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.positions.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/20 transition hover:from-violet-700 hover:to-indigo-700">
                    <i class="bi bi-plus-circle me-2"></i> Thêm mới
                </a>
                <a href="{{ route('admin.positions.trash') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Xem chức vụ đã xóa
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên chức vụ</th>
                        <th>Lương cơ bản</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        <tr>
                            <td>{{ $loop->iteration + ($positions->currentPage() - 1) * $positions->perPage() }}</td>
                            <td>{{ $position->position_name }}</td>
                            <td>{{ number_format($position->base_salary, 0, ',', '.') }} ₫</td>
                            <td>
                                <span class="badge {{ $position->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($position->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil me-1"></i> Sửa
                                    </a>

                                    <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa chức vụ này không?')">
                                            <i class="bi bi-trash me-1"></i> Xóa mềm
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có chức vụ nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $positions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-admin-layout>
