<x-admin-layout title="Chức vụ đã xóa">
    <div class="admin-card p-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-2xl font-bold text-slate-800">Danh sách chức vụ đã xóa</h2>
                <p class="text-sm text-slate-500">Các chức vụ đã bị xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route('admin.positions') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên chức vụ</th>
                        <th>Lương cơ bản</th>
                        <th>Đã xóa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        <tr>
                            <td>{{ $loop->iteration + ($positions->currentPage() - 1) * $positions->perPage() }}</td>
                            <td>{{ $position->position_name }}</td>
                            <td>{{ number_format($position->base_salary, 0, ',', '.') }} ₫</td>
                            <td>{{ $position->deleted_at ? $position->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.positions.restore', $position->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Khôi phục
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.positions.forceDelete', $position->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa cứng sẽ xóa hoàn toàn dữ liệu và không thể khôi phục. Bạn chắc chắn không?')">
                                            <i class="bi bi-trash3 me-1"></i> Xóa cứng
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có chức vụ đã xóa.</td>
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
