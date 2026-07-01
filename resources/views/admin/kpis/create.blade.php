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
                            Tên KPI <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Ví dụ: Hoàn thành công việc đúng hạn">

                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mô tả --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            rows="3"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Mô tả ngắn về KPI">{{ old('description') }}</textarea>

                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phòng ban áp dụng (nhiều) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Phòng ban áp dụng <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-4 rounded-xl border border-slate-300">
                            @foreach($departments as $department)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="departments[]"
                                        value="{{ $department->id }}"
                                        {{ collect(old('departments'))->contains($department->id) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    {{ $department->department_name }}
                                </label>
                            @endforeach
                        </div>

                        @error('departments')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Chức vụ áp dụng --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Chức vụ áp dụng
                        </label>

                        <div class="flex flex-wrap gap-4 p-4 rounded-xl border border-slate-300">
                            @foreach(\App\Models\KPI::POSITIONS as $value => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="positions[]"
                                        value="{{ $value }}"
                                        {{ collect(old('positions'))->contains($value) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                        @error('positions')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mục tiêu --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mục tiêu
                        </label>

                        <input
                            type="text"
                            name="target"
                            value="{{ old('target') }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Ví dụ: 30 Task, 100%, 300 triệu">

                        @error('target')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Đơn vị đo --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Đơn vị đo
                        </label>

                        <select
                            name="unit"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                            <option value="">-- Chọn đơn vị --</option>
                            @foreach(['%', 'Task', 'Doanh số', 'Điểm', 'Số lần'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                            @endforeach
                        </select>

                        @error('unit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
                            value="{{ old('weight') }}"
                            min="1"
                            max="100"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Ví dụ: 30">

                        @error('weight')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Điểm tối đa --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Điểm tối đa
                        </label>

                        <input
                            type="number"
                            name="max_score"
                            value="{{ old('max_score', 100) }}"
                            min="1"
                            max="1000"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Mặc định 100">

                        @error('max_score')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kỳ đánh giá --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kỳ đánh giá
                        </label>

                        <select
                            name="period"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                            <option value="">-- Chọn kỳ --</option>
                            <option value="month" {{ old('period') == 'month' ? 'selected' : '' }}>Tháng</option>
                            <option value="quarter" {{ old('period') == 'quarter' ? 'selected' : '' }}>Quý</option>
                            <option value="year" {{ old('period') == 'year' ? 'selected' : '' }}>Năm</option>
                        </select>

                        @error('period')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ngày bắt đầu --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày bắt đầu
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            value="{{ old('start_date') }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">

                        @error('start_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ngày kết thúc --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày kết thúc
                        </label>

                        <input
                            type="date"
                            name="end_date"
                            value="{{ old('end_date') }}"
                            class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">

                        @error('end_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Ngừng áp dụng</option>
                        </select>

                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
