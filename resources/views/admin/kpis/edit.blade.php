<x-admin-layout title="Chỉnh sửa KPI">

    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                Chỉnh sửa KPI
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Cập nhật thông tin KPI
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">

            <form action="{{ route('admin.kpis.update', $kpi->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Mã KPI --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mã KPI <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="code"
                            value="{{ old('code', $kpi->code) }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Ví dụ: KPI001">

                        @error('code')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Trạng thái --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Trạng thái <span class="text-red-500">*</span>
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                            <option value="active" {{ old('status', $kpi->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('status', $kpi->status) == 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>

                        @error('status')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Tên KPI --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tên KPI <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title', $kpi->title) }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Nhập tên KPI">

                        @error('title')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Mô tả --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Nhập mô tả KPI">{{ old('description', $kpi->description) }}</textarea>

                        @error('description')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Trọng số --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Trọng số (%) <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="number"
                            name="weight"
                            value="{{ old('weight', $kpi->weight) }}"
                            min="1"
                            max="100"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Ví dụ: 30">

                        @error('weight')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Phòng ban --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Phòng ban <span class="text-red-500">*</span>
                        </label>

                        <select
                            name="department_id"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">

                            <option value="">
                                -- Chọn phòng ban --
                            </option>

                            @foreach($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $kpi->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach

                        </select>

                        @error('department_id')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div class="flex justify-end gap-3 mt-8">

                    <a href="{{ route('admin.kpis.index') }}"
                       class="px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Cập nhật KPI
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-admin-layout>