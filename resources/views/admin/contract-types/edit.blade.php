<x-admin-layout title="Sửa loại hợp đồng">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa loại hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">Cập nhật thông tin loại hợp đồng.</p>
            </div>
            <a href="{{ route('admin.contract-types.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ route('admin.contract-types.update', $contractType) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="contract_name" class="block text-sm font-semibold text-slate-700">Tên loại hợp đồng</label>
                    <input type="text" id="contract_name" name="contract_name" value="{{ old('contract_name', $contractType->contract_name) }}"
                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                    @error('contract_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="duration_month" class="block text-sm font-semibold text-slate-700">Thời hạn (tháng)</label>
                    <input type="number" id="duration_month" name="duration_month" value="{{ old('duration_month', $contractType->duration_month) }}"
                           class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" min="1" required>
                    @error('duration_month') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.contract-types.index') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">Hủy</a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
