<x-admin-layout title="Thêm KPI">

    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                Thêm KPI mới
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Tạo một KPI mới cho hệ thống
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">

            <form action="{{ route('admin.kpis.store') }}" method="POST">

                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Tên KPI --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tên KPI
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
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
                            placeholder="Nhập mô tả KPI">{{ old('description') }}</textarea>

                        @error('description')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Trọng số --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Trọng số (%)
                        </label>

                        <input
                            type="number"
                            name="weight"
                            value="{{ old('weight') }}"
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
                            Phòng ban
                        </label>

                        <select
                            name="department_id"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">

                            <option value="">
                                -- Chọn phòng ban --
                            </option>

                            @foreach($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                        Thêm KPI
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-admin-layout>