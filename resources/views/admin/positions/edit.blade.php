<x-admin-layout title="Sửa chức vụ">
    <div class="admin-card p-6">
        <div class="mb-6">
            <h2 class="mb-1 text-2xl font-bold text-slate-800">Sửa chức vụ</h2>
            <p class="text-sm text-slate-500">Cập nhật thông tin chức vụ bên dưới</p>
        </div>

        <form action="{{ route('admin.positions.update', $position) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="position_name" class="form-label fw-bold">Tên chức vụ <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control @error('position_name') is-invalid @enderror" 
                    id="position_name" 
                    name="position_name" 
                    placeholder="Nhập tên chức vụ"
                    value="{{ old('position_name', $position->position_name) }}"
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
                    value="{{ old('base_salary', $position->base_salary) }}"
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
                    <option value="active" {{ old('status', $position->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ old('status', $position->status) === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
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
                >{{ old('description', $position->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i> Cập nhật
                </button>
                <a href="{{ route('admin.positions') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
