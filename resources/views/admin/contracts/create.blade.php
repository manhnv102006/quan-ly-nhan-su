<x-admin-layout title="Tạo hợp đồng mới">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tạo hợp đồng mới</h2>
                <p class="text-sm text-slate-500">Nhập thông tin hợp đồng lao động cho nhân viên đang hoạt động.</p>
            </div>
            <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Quay lại danh sách</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.contracts.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.contracts.partials.form-fields', [
                    'employees' => $employees,
                    'contractTypes' => $contractTypes,
                    'departments' => $departments,
                    'positions' => $positions,
                    'nextCode' => $nextCode,
                ])

                <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet px-6">Lưu hợp đồng</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
