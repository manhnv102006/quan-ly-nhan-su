<x-admin-layout title="Quản lý chức vụ">
    <div class="admin-card p-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-2xl font-bold text-slate-800">Danh sách chức vụ</h2>
                <p class="text-sm text-slate-500">Quản lý chức vụ đang hoạt động và xóa mềm.</p>
            </div>
            <a href="{{ route('admin.positions.trash') }}" class="btn btn-outline-secondary">Xem chức vụ đã xóa</a>
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
                                <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa chức vụ này không?')">
                                        Xóa mềm
                                    </button>
                                </form>
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
