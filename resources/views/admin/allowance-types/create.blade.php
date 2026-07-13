<x-admin-layout title="Thêm loại phụ cấp">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm loại phụ cấp</h2>
                <p class="text-sm text-slate-500 mt-1">Loại mới sẽ xuất hiện trong form tạo hợp đồng.</p>
            </div>
            <a href="{{ route('admin.allowance-types.index') }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-6">
            <form action="{{ route('admin.allowance-types.store') }}" method="POST">
                @csrf
                @include('admin.allowance-types.partials.form-fields')
                <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.allowance-types.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
