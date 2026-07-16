@php
    $user = Auth::user();
    $isManager = $user->role->name === 'manager';
    $layout = $isManager ? 'manager-layout' : 'employee-layout';
    $layoutParams = [
        'title'    => 'Tạo đơn xin về sớm',
        'subtitle' => 'Điền thông tin để gửi yêu cầu về sớm.',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="max-w-xl">
        <a href="{{ route('employee.early-leave.index') }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition mb-6 font-semibold">
            <span>←</span> Quay lại danh sách
        </a>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-2">Đơn xin về sớm</h2>
            <p class="text-xs text-slate-400 mb-6">
                Nếu đơn được duyệt, bạn sẽ được check-out sớm và vẫn tính đủ công.<br>
                Nếu không có đơn duyệt, check-out sớm sẽ chỉ tính <strong class="text-rose-500">0.5 công</strong>.
            </p>

            <form action="{{ route('employee.early-leave.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="request_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                        Ngày xin về sớm <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="request_date" name="request_date"
                           min="{{ today()->toDateString() }}"
                           value="{{ old('request_date', today()->toDateString()) }}"
                           required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                    @error('request_date')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="leave_time" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                        Giờ muốn về <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" id="leave_time" name="leave_time"
                           value="{{ old('leave_time') }}"
                           required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                    @error('leave_time')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reason" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                        Lý do <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" rows="4" required
                              placeholder="Ví dụ: Đi khám bệnh, có việc gia đình khẩn cấp..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm resize-none">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-3 rounded-xl bg-violet-600 text-white text-sm font-bold shadow-md shadow-violet-500/20 hover:bg-violet-700 transition">
                        Gửi đơn
                    </button>
                    <a href="{{ route('employee.early-leave.index') }}"
                       class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold text-center hover:bg-slate-50 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-dynamic-component>
