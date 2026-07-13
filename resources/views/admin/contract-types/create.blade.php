<x-admin-layout title="Thêm loại hợp đồng mới">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm loại hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">Thử việc, xác định thời hạn, thời vụ, CTV...</p>
            </div>
            <a href="{{ route('admin.contract-types.index') }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-6">
            <form action="{{ route('admin.contract-types.store') }}" method="POST" class="space-y-4">
                @csrf
                @include('admin.contract-types.partials.form-fields')
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.contract-types.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
