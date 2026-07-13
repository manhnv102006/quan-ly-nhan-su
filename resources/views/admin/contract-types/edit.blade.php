<x-admin-layout title="Sửa loại hợp đồng">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa loại hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $contractType->contract_name }}</p>
            </div>
            <a href="{{ route('admin.contract-types.index') }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-6">
            <form action="{{ route('admin.contract-types.update', $contractType) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                @include('admin.contract-types.partials.form-fields', ['contractType' => $contractType])
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.contract-types.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
