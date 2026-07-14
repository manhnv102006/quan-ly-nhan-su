<x-admin-layout title="Thêm Ngày Lễ">

    <div class="space-y-6 max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Thêm Ngày Lễ / Sự kiện
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Điền thông tin để hệ thống tự động chấm công cho nhân viên
                </p>
            </div>
            
            <a href="{{ route('admin.holidays.index') }}" class="text-sm text-violet-600 font-medium hover:text-violet-700">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden p-8">
            <form action="{{ route('admin.holidays.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tên sự kiện <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="VD: Nghỉ Tết Nguyên Đán, Du lịch Phú Quốc..."
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Loại sự kiện <span class="text-rose-500">*</span></label>
                        <select name="type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            <option value="public_holiday" {{ old('type') == 'public_holiday' ? 'selected' : '' }}>Nghỉ Lễ, Tết</option>
                            <option value="company_trip" {{ old('type') == 'company_trip' ? 'selected' : '' }}>Du lịch / Sự kiện công ty</option>
                        </select>
                        @error('type') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2 grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Từ ngày <span class="text-rose-500">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            @error('start_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Đến ngày <span class="text-rose-500">*</span></label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">
                            @error('end_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ghi chú thêm</label>
                        <textarea name="description" rows="3" placeholder="Ghi chú chi tiết về sự kiện này..."
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all">{{ old('description') }}</textarea>
                        @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.holidays.index') }}" class="px-5 py-2.5 text-slate-600 font-medium hover:bg-slate-100 rounded-xl transition">
                        Hủy
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-violet-600 text-white font-medium rounded-xl shadow-lg shadow-violet-500/30 hover:bg-violet-700 hover:-translate-y-0.5 transition-all">
                        Lưu Sự Kiện
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
