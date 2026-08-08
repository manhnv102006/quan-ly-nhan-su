@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isAdmin = $roleName === 'admin';
    $isManager = $roleName === 'manager';
    $leaveCapacityPercent = $leaveCapacityPercent ?? 30;

    $navigation = \App\Support\SelfServiceLayout::navigation();
    $layout = \App\Support\SelfServiceLayout::component($roleName);
    $layoutParams = $isAdmin
        ? ['title' => 'Tạo đơn nghỉ phép']
        : [
            'title' => 'Tạo đơn nghỉ phép',
            'subtitle' => 'Điền đầy đủ thông tin để gửi đơn xin nghỉ phép.',
        ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="max-w-2xl space-y-6">
        <a href="{{ $isManager ? route('manager.leave-requests.index') : route('employee.leave-requests') }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition font-semibold">
            <span>←</span> {{ $isManager ? 'Quay lại quản lý nghỉ phép' : 'Quay lại danh sách' }}
        </a>

        @include('employee.partials.leave-request-rules', ['leaveCapacityPercent' => $leaveCapacityPercent])

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Đơn xin nghỉ phép mới</h2>

            <x-leave-capacity-alert field="leave_capacity" class="mb-6" />

            <form id="leave-request-form" action="{{ route('employee.leave-requests.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="leave_type" class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại nghỉ phép <span class="text-rose-500">*</span></label>
                    <select id="leave_type" name="leave_type" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        <option value="">-- Chọn loại nghỉ phép --</option>
                        @foreach (\App\Models\LeaveRequest::LEAVE_TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('leave_type') == $value)>{{ $label }}</option>
                        @endforeach
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
                    <button type="submit" id="leave-request-submit"
                            class="flex-1 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-500/20 transition disabled:opacity-60 disabled:pointer-events-none">
                        Gửi đơn xin nghỉ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('leave-request-form');
            const leaveType = document.getElementById('leave_type');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            function syncHalfDayEndDate() {
                if (leaveType?.value === 'half_day' && startDate?.value) {
                    endDate.value = startDate.value;
                    endDate.readOnly = true;
                    endDate.classList.add('bg-slate-50');
                } else if (endDate) {
                    endDate.readOnly = false;
                    endDate.classList.remove('bg-slate-50');
                }
            }

            leaveType?.addEventListener('change', syncHalfDayEndDate);
            startDate?.addEventListener('change', syncHalfDayEndDate);
            syncHalfDayEndDate();

            form?.addEventListener('submit', function () {
                const btn = document.getElementById('leave-request-submit');
                if (btn && !btn.disabled) {
                    btn.disabled = true;
                    btn.textContent = 'Đang gửi...';
                }
            });
        })();
    </script>

</x-dynamic-component>
