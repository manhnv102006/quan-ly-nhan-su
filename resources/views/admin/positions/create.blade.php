<x-admin-layout title="Thêm chức vụ mới">
    <div class="admin-card p-6">
        <div class="mb-6">
            <h2 class="mb-1 text-2xl font-bold text-slate-800">Thêm chức vụ mới</h2>
            <p class="text-sm text-slate-500">Điền thông tin chức vụ mới bên dưới</p>
        </div>

        <form action="{{ route('admin.positions.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="position_name" class="form-label fw-bold">Tên chức vụ <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control @error('position_name') is-invalid @enderror"
                    id="position_name"
                    name="position_name"
                    placeholder="Nhập tên chức vụ"
                    value="{{ old('position_name') }}"
                    required
                >
                @error('position_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="base_salary" class="form-label fw-bold">Lương cơ bản (VND) <span class="text-danger">*</span></label>
                <input
                    type="number"
                    class="form-control @error('base_salary') is-invalid @enderror"
                    id="base_salary"
                    name="base_salary"
                    placeholder="Nhập lương cơ bản"
                    value="{{ old('base_salary') }}"
                    min="0"
                    step="1000"
                    required
                >
                @error('base_salary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="status" class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                <select
                    class="form-select @error('status') is-invalid @enderror"
                    id="status"
                    name="status"
                    required
                >
                    <option value="">-- Chọn trạng thái --</option>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-bold">Mô tả</label>
                <textarea
                    class="form-control @error('description') is-invalid @enderror"
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Nhập mô tả chức vụ (không bắt buộc)"
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/20 transition hover:from-violet-700 hover:to-indigo-700">
                    <i class="bi bi-plus-circle me-2"></i> Thêm mới
                </button>
                <a href="{{ route('admin.positions') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    <i class="bi bi-x-circle me-2"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
