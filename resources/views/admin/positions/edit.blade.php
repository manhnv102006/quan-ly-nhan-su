<x-admin-layout title="Sửa chức vụ">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa chức vụ</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Cập nhật thông tin: {{ $position->position_name }}
                </p>
            </div>

            <a href="{{ route('admin.positions') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 max-w-3xl">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.positions.update', $position) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="position_name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên chức vụ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="position_name" name="position_name"
                           value="{{ old('position_name', $position->position_name) }}"
                           placeholder="Nhập tên chức vụ" required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('position_name') border-red-400 @enderror">
                    @error('position_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="base_salary" class="block text-sm font-semibold text-slate-700 mb-2">
                        Lương cơ bản (VND) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="base_salary" name="base_salary"
                           value="{{ old('base_salary', $position->base_salary) }}"
                           placeholder="Nhập lương cơ bản" min="0" step="1000" required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('base_salary') border-red-400 @enderror">
                    @error('base_salary')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="allowance" class="block text-sm font-semibold text-slate-700 mb-2">
                        Phụ cấp chức vụ (VND)
                    </label>
                    <input type="number" id="allowance" name="allowance"
                           value="{{ old('allowance', (int)$position->allowance) }}"
                           placeholder="Nhập phụ cấp chức vụ" min="0" step="1000"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('allowance') border-red-400 @enderror">
                    @error('allowance')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="active" @selected(old('status', $position->status) === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status', $position->status) === 'inactive')>Không hoạt động</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Mô tả</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Mô tả chức vụ (không bắt buộc)"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-y @error('description') border-red-400 @enderror">{{ old('description', $position->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.positions') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-admin-layout>
