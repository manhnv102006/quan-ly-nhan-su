@php
    $user = Auth::user();

    $navigation = [
        ['label' => 'Dashboard',  'href' => route('employee.dashboard'),        'route' => 'employee.dashboard',         'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
        ['label' => 'Chấm công',  'href' => route('attendance.index'),           'route' => 'attendance.index',           'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        'note' => 'Check-in / Check-out'],
        ['label' => 'Tăng ca',    'href' => route('employee.overtime-requests'), 'route' => 'employee.overtime-requests*','icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        'note' => 'Đơn xin tăng ca'],
        ['label' => 'Nghỉ phép',  'href' => route('employee.leave-requests'),    'route' => 'employee.leave-requests*',  'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              'note' => 'Đơn xin nghỉ phép'],
        ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'),    'route' => 'employee.payrolls*',        'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                                                                                                                                                                                                                                               'note' => 'Tổng thu nhập'],
        ['label' => 'Hồ sơ',     'href' => route('profile.edit'),               'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          'note' => 'Thông tin cá nhân'],
    ];
@endphp

<x-staff-layout title="Tạo đơn tăng ca" role="employee" :navigation="$navigation">

    <div class="max-w-xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('employee.overtime-requests') }}"
               class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Tạo đơn tăng ca</h1>
                <p class="text-xs text-slate-500">Điền thông tin và lý do tăng ca để gửi phê duyệt.</p>
            </div>
        </div>

        {{-- Banner điền sẵn --}}
        @if (!empty($prefill['overtime_date']))
            <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-800 font-medium">
                    Thông tin đã được điền sẵn dựa trên giờ chấm công hôm nay. Kiểm tra lại trước khi gửi.
                </p>
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ route('employee.overtime-requests.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Ngày tăng ca <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="overtime_date"
                           value="{{ old('overtime_date', $prefill['overtime_date'] ?? '') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition">
                    @error('overtime_date')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Giờ bắt đầu <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="start_time"
                               value="{{ old('start_time', $prefill['start_time'] ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition">
                        @error('start_time')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Giờ kết thúc <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="end_time"
                               value="{{ old('end_time', $prefill['end_time'] ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition">
                        @error('end_time')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Preview giờ realtime --}}
                <div id="hours-preview" class="hidden bg-slate-50 rounded-xl px-4 py-3 text-sm text-slate-600">
                    Thời gian tăng ca: <span id="hours-value" class="font-bold text-amber-700"></span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Lý do tăng ca <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" rows="4"
                              placeholder="Ví dụ: Xử lý công việc tồn đọng, hoàn thiện báo cáo cuối tháng..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition resize-none">{{ old('reason') }}</textarea>
                    @error('reason')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold shadow-md shadow-amber-500/20 hover:bg-amber-700 transition">
                        Gửi đơn tăng ca
                    </button>
                    <a href="{{ route('employee.overtime-requests') }}"
                       class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold text-center hover:bg-slate-200 transition">
                        Huỷ
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const startInput = document.querySelector('input[name="start_time"]');
        const endInput   = document.querySelector('input[name="end_time"]');
        const preview    = document.getElementById('hours-preview');
        const hoursVal   = document.getElementById('hours-value');

        function updatePreview() {
            const start = startInput.value;
            const end   = endInput.value;
            if (!start || !end) { preview.classList.add('hidden'); return; }

            const [sh, sm] = start.split(':').map(Number);
            const [eh, em] = end.split(':').map(Number);
            const diff = (eh * 60 + em) - (sh * 60 + sm);

            if (diff <= 0) { preview.classList.add('hidden'); return; }

            const h = Math.floor(diff / 60);
            const m = diff % 60;
            hoursVal.textContent = h > 0 ? `${h} giờ${m > 0 ? ' ' + m + ' phút' : ''}` : `${m} phút`;
            preview.classList.remove('hidden');
        }

        startInput.addEventListener('change', updatePreview);
        endInput.addEventListener('change', updatePreview);
        updatePreview();
    </script>

</x-staff-layout>