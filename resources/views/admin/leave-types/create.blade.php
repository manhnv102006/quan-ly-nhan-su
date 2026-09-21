<x-admin-layout title="Thêm loại nghỉ phép">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm loại nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">Loại mới sẽ xuất hiện ngay trong form tạo đơn nghỉ của nhân viên.</p>
            </div>
            <a href="{{ route('admin.leave-types.index') }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-6">
            <form action="{{ route('admin.leave-types.store') }}" method="POST">
                @csrf
                @include('admin.leave-types.partials.form-fields')
                <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.leave-types.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
