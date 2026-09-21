<x-admin-layout title="Sửa loại nghỉ phép">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa loại nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">
                    <span class="font-semibold text-slate-700">{{ $leaveType->name }}</span>
                    <span class="font-mono text-xs text-slate-400">({{ $leaveType->code }})</span>
                    @if ($leaveType->is_system)
                        — loại hệ thống, chỉ sửa được cấu hình, không đổi mã và không xóa.
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.leave-types.index') }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-6">
            <form action="{{ route('admin.leave-types.update', $leaveType) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.leave-types.partials.form-fields')
                <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.leave-types.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
