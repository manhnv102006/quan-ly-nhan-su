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

    <div class="w-full space-y-6">
        <a href="{{ $isManager ? route('manager.leave-requests.index') : route('employee.leave-requests') }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition font-semibold">
            <span>←</span> {{ $isManager ? 'Quay lại quản lý nghỉ phép' : 'Quay lại danh sách' }}
        </a>

        @include('employee.partials.leave-paid-balance', ['leaveBalance' => $leaveBalance ?? null])

        @include('employee.partials.leave-request-rules', [
            'leaveCapacityPercent' => $leaveCapacityPercent,
            'typeLabels' => $leaveTypeOptions ?? \App\Models\LeaveRequest::leaveTypeLabels(),
        ])

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Đơn xin nghỉ phép mới</h2>

            <x-leave-capacity-alert field="leave_capacity" class="mb-6" />

            <form id="leave-request-form" action="{{ route('employee.leave-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="leave_type" class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại nghỉ phép <span class="text-rose-500">*</span></label>
                    <select id="leave_type" name="leave_type" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        <option value="">-- Chọn loại nghỉ phép --</option>
                        @foreach ($leaveTypeOptions ?? \App\Models\LeaveRequest::leaveTypeLabels() as $value => $label)
                            <option value="{{ $value }}" @selected(old('leave_type') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('leave_type')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div id="half-day-period-wrap" class="hidden rounded-2xl border border-violet-200 bg-violet-50/70 p-4 sm:p-5">
                    <label for="half_day_period" class="block text-xs font-bold text-violet-900 uppercase mb-2">
                        Buổi nghỉ <span class="text-rose-500">*</span>
                    </label>
                    <select id="half_day_period" name="half_day_period"
                            class="w-full rounded-xl border border-violet-200 bg-white px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <option value="">-- Chọn buổi nghỉ --</option>
                        @foreach (\App\Models\LeaveRequest::HALF_DAY_PERIOD_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('half_day_period') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-[11px] leading-relaxed text-violet-900/80">Nghỉ nửa ngày chỉ áp dụng trong <strong>một ngày</strong>, trừ <strong>0,5 ngày</strong>; không áp dụng Chủ nhật / ngày Lễ.</p>
                    @error('half_day_period')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                            <span id="start-date-label">Ngày bắt đầu</span> <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}"
                               min="{{ today()->toDateString() }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        @error('start_date')
                            <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="end-date-wrap">
                        <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">Ngày kết thúc <span class="text-rose-500">*</span></label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                               min="{{ today()->toDateString() }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">
                        @error('end_date')
                            <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="paid-balance-notice" class="hidden rounded-2xl border px-4 py-3 text-sm font-medium leading-relaxed" role="alert"></div>

                <div>
                    <label for="reason" class="block text-xs font-bold text-slate-500 uppercase mb-2">Lý do xin nghỉ <span class="text-rose-500">*</span></label>
                    <textarea id="reason" name="reason" rows="4" required placeholder="Nhập lý do chi tiết..."
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition text-sm">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div id="supporting-document-wrap" class="hidden rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5">
                    <label for="supporting_document" class="block text-xs font-bold text-amber-900 uppercase mb-2">
                        Giấy tờ minh chứng <span class="text-rose-500">*</span>
                    </label>
                    <p id="supporting-document-hint" class="mb-3 text-xs leading-relaxed text-amber-900/90"></p>
                    <input type="file" id="supporting_document" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-sky-700 hover:file:bg-sky-50">
                    <p class="mt-2 text-[11px] text-amber-800/80">PDF, JPG hoặc PNG — tối đa 5MB.</p>
                    @error('supporting_document')
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
            const documentWrap = document.getElementById('supporting-document-wrap');
            const documentInput = document.getElementById('supporting_document');
            const documentHint = document.getElementById('supporting-document-hint');
            const halfDayWrap = document.getElementById('half-day-period-wrap');
            const halfDayPeriod = document.getElementById('half_day_period');
            const endDateWrap = document.getElementById('end-date-wrap');
            const startDateLabel = document.getElementById('start-date-label');
            const typesRequiringDocument = @json($leaveTypesRequiringDocument ?? []);
            const documentHints = @json($leaveDocumentHints ?? []);
            const today = @json(today()->toDateString());
            const previewUrl = @json(route('employee.leave-requests.paid-balance-preview'));
            const paidNotice = document.getElementById('paid-balance-notice');
            let previewTimer = null;
            let latestPreview = null;

            function hidePaidNotice() {
                latestPreview = null;
                if (!paidNotice) {
                    return;
                }
                paidNotice.classList.add('hidden');
                paidNotice.textContent = '';
            }

            function showPaidNotice(message, blocked) {
                if (!paidNotice) {
                    return;
                }
                paidNotice.textContent = message;
                paidNotice.className = blocked
                    ? 'rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium leading-relaxed text-rose-800'
                    : 'rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium leading-relaxed text-amber-950';
            }

            function schedulePaidPreview() {
                clearTimeout(previewTimer);

                if (leaveType?.value !== 'annual' || !startDate?.value || !endDate?.value) {
                    hidePaidNotice();
                    return;
                }

                previewTimer = setTimeout(fetchPaidPreview, 250);
            }

            async function fetchPaidPreview() {
                const params = new URLSearchParams({
                    leave_type: leaveType.value,
                    start_date: startDate.value,
                    end_date: leaveType.value === 'half_day' ? startDate.value : endDate.value,
                });

                try {
                    const response = await fetch(previewUrl + '?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        hidePaidNotice();
                        return;
                    }

                    const data = await response.json();
                    latestPreview = data;

                    if (!data.applies || !data.message) {
                        hidePaidNotice();
                        latestPreview = data;
                        return;
                    }

                    showPaidNotice(data.message, Boolean(data.blocked));
                } catch (error) {
                    hidePaidNotice();
                }
            }

            function syncHalfDayFields() {
                const isHalfDay = leaveType?.value === 'half_day';

                if (halfDayWrap) {
                    halfDayWrap.classList.toggle('hidden', !isHalfDay);
                }

                if (halfDayPeriod) {
                    halfDayPeriod.required = isHalfDay;
                    if (!isHalfDay) {
                        halfDayPeriod.value = '';
                    }
                }

                if (endDateWrap) {
                    endDateWrap.classList.toggle('hidden', isHalfDay);
                }

                if (startDateLabel) {
                    startDateLabel.textContent = isHalfDay ? 'Ngày nghỉ' : 'Ngày bắt đầu';
                }
            }

            function syncDocumentRequirement() {
                const type = leaveType?.value ?? '';
                const required = typesRequiringDocument.includes(type);

                if (documentWrap) {
                    documentWrap.classList.toggle('hidden', !required);
                }

                if (documentInput) {
                    documentInput.required = required;
                    if (!required) {
                        documentInput.value = '';
                    }
                }

                if (documentHint) {
                    documentHint.textContent = documentHints[type] ?? 'Vui lòng đính kèm giấy tờ minh chứng.';
                }
            }

            function syncDateBounds() {
                if (startDate) {
                    startDate.min = today;
                }

                const minEnd = startDate?.value && startDate.value >= today ? startDate.value : today;
                if (endDate && leaveType?.value !== 'half_day') {
                    endDate.min = minEnd;
                }

                if (leaveType?.value === 'half_day' && startDate?.value) {
                    endDate.value = startDate.value;
                    endDate.readOnly = true;
                    endDate.classList.add('bg-slate-50');
                } else if (endDate) {
                    endDate.readOnly = false;
                    endDate.classList.remove('bg-slate-50');
                }
            }

            leaveType?.addEventListener('change', syncDateBounds);
            leaveType?.addEventListener('change', syncDocumentRequirement);
            leaveType?.addEventListener('change', syncHalfDayFields);
            leaveType?.addEventListener('change', schedulePaidPreview);
            startDate?.addEventListener('change', syncDateBounds);
            startDate?.addEventListener('change', schedulePaidPreview);
            endDate?.addEventListener('change', schedulePaidPreview);
            syncDateBounds();
            syncDocumentRequirement();
            syncHalfDayFields();
            schedulePaidPreview();

            form?.addEventListener('submit', function (event) {
                if (latestPreview?.split && form.dataset.confirmedSplit !== '1') {
                    event.preventDefault();
                    const accepted = window.confirm((latestPreview.message || 'Đơn này vượt số ngày hưởng lương.') + '\n\nBạn vẫn muốn gửi đơn?');
                    if (!accepted) {
                        return;
                    }
                    form.dataset.confirmedSplit = '1';
                    form.requestSubmit();
                    return;
                }

                const btn = document.getElementById('leave-request-submit');
                if (btn && !btn.disabled) {
                    btn.disabled = true;
                    btn.textContent = 'Đang gửi...';
                }
            });
        })();
    </script>

</x-dynamic-component>
