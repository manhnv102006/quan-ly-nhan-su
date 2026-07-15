@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = ['title' => 'Tạo đơn tăng ca', 'subtitle' => 'Gửi yêu cầu tăng ca cho quản lý phê duyệt.'];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

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
        @if (!empty($prefill['work_date']))
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
                    <input type="date" name="work_date"
                           value="{{ old('work_date', $prefill['work_date'] ?? now()->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition">
                    @error('work_date')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
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

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Loại ngày tăng ca <span class="text-rose-500">*</span>
                    </label>
                    <select name="rate_multiplier" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition bg-white">
                        <option value="1.5" @selected(old('rate_multiplier') == '1.5')>Ngày thường (x1.5)</option>
                        <option value="2.0" @selected(old('rate_multiplier') == '2.0')>Ngày nghỉ cuối tuần (x2.0)</option>
                        <option value="3.0" @selected(old('rate_multiplier') == '3.0')>Ngày Lễ, Tết (x3.0)</option>
                    </select>
                    @error('rate_multiplier')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
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

</x-dynamic-component>