@php
    $user = Auth::user();
    $isAdmin = $user->role->name === 'admin';
    $isManager = $user->role->name === 'manager';

    $navigation = $isManager
        ? \App\Support\ManagerNavigation::items()
        : \App\Support\EmployeeNavigation::items();

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
    $layoutParams = $isAdmin
        ? ['title' => 'Tạo đơn nghỉ phép']
        : [
            'title' => 'Tạo đơn nghỉ phép',
            'subtitle' => 'Điền đầy đủ thông tin để gửi đơn xin nghỉ phép.',
            'role' => $isManager ? 'manager' : 'employee',
            'navigation' => $navigation
        ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="max-w-2xl">
        <a href="{{ route('employee.leave-requests') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition mb-6 font-semibold">
            <span>←</span> Quay lại danh sách
        </a>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Đơn xin nghỉ phép mới</h2>

            <form action="{{ route('employee.leave-requests.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="leave_type" class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại nghỉ phép <span class="text-rose-500">*</span></label>
                    <select id="leave_type" name="leave_type" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        <option value="">-- Chọn loại nghỉ phép --</option>
                        <option value="annual" @selected(old('leave_type') == 'annual')>Nghỉ phép năm</option>
                        <option value="sick" @selected(old('leave_type') == 'sick')>Nghỉ ốm</option>
                        <option value="unpaid" @selected(old('leave_type') == 'unpaid')>Nghỉ không lương</option>
                    </select>
                    @error('leave_type')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">Ngày bắt đầu <span class="text-rose-500">*</span></label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        @error('start_date')
                            <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">Ngày kết thúc <span class="text-rose-500">*</span></label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        @error('end_date')
                            <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="reason" class="block text-xs font-bold text-slate-500 uppercase mb-2">Lý do xin nghỉ <span class="text-rose-500">*</span></label>
                    <textarea id="reason" name="reason" rows="4" required placeholder="Nhập lý do chi tiết..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ route('employee.leave-requests') }}"
                       class="flex-1 text-center px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Hủy bỏ
                    </a>
                    <button type="submit"
                            class="flex-1 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-500/20 transition">
                        Gửi đơn xin nghỉ
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-dynamic-component>
