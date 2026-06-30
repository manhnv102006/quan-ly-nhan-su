<x-admin-layout title="Cập nhật hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa hợp đồng</h2>
                <p class="text-sm text-slate-500">
                    Mã: <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span>
                    · Chỉ sửa được hợp đồng đang soạn hoặc đang hiệu lực.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Xem chi tiết</a>
                <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Danh sách</a>
            </div>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.contracts.update', $contract) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.contracts.partials.form-fields', [
                    'contract' => $contract,
                    'employees' => $employees,
                    'contractTypes' => $contractTypes,
                    'departments' => $departments,
                    'positions' => $positions,
                ])

                <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet px-6">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
