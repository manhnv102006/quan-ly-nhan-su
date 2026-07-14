<x-admin-layout title="Sửa phòng ban">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa phòng ban</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Cập nhật thông tin phòng ban: {{ $department->department_name }}
                </p>
            </div>

            <a href="{{ route('admin.departments') }}"
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

            <form action="{{ route('admin.departments.update', $department->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="department_code" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mã phòng ban <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="department_code"
                        name="department_code"
                        value="{{ old('department_code', $department->department_code) }}"
                        placeholder="VD: HR, IT, SALE"
                        maxlength="20"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('department_code') border-red-400 @enderror"
                    >
                    @error('department_code')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="department_name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên phòng ban <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="department_name"
                        name="department_name"
                        value="{{ old('department_name', $department->department_name) }}"
                        placeholder="Nhập tên phòng ban"
                        maxlength="100"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('department_name') border-red-400 @enderror"
                    >
                    @error('department_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.departments.partials.max-employees-field', ['department' => $department])

                <div>
                    <label for="manager_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Quản lý phòng ban
                    </label>
                    <select
                        id="manager_id"
                        name="manager_id"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('manager_id') border-red-400 @enderror"
                    >
                        <option value="">-- Chưa chỉ định --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('manager_id', $department->manager_id) == $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror"
                    >
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="active" @selected(old('status', $department->status) === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status', $department->status) === 'inactive')>Không hoạt động</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mô tả
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Mô tả chức năng phòng ban (không bắt buộc)"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-y @error('description') border-red-400 @enderror"
                    >{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.departments') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-admin-layout>
