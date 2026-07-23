@php
    $role = Auth::user()->role->name ?? 'employee';

    $layout = \App\Support\SelfServiceLayout::component($role);

    $layoutParams = [
        'title' => 'Đăng ký NPT mới',
        'subtitle' => 'Kế toán duyệt · Áp dụng GT phụ thuộc khi được chấp nhận',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-2xl space-y-6" x-data="nptRegistrationForm()">
        <a href="{{ route('employee.tax-dependents.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            ← Quay lại danh sách
        </a>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-2 text-lg font-bold text-slate-800">Thông tin người phụ thuộc</h2>
            <p class="mb-4 text-sm text-slate-500">
                Giảm trừ mặc định: <strong class="text-violet-700">{{ number_format($defaultDeduction, 0, ',', '.') }}₫/tháng</strong> sau khi kế toán duyệt.
            </p>

            <div class="mb-6 rounded-xl border border-violet-100 bg-violet-50/60 px-4 py-3 text-xs text-violet-900">
                <p class="font-bold uppercase tracking-wide text-violet-800">Giấy tờ bắt buộc</p>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    @foreach($documentGuide as $row)
                        <li>{{ $row['summary'] }}</li>
                    @endforeach
                    <li>Quan hệ khác: đính kèm giấy tờ liên quan (PDF/JPG/PNG, tối đa 5MB/tệp)</li>
                </ul>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('employee.tax-dependents.store') }}" class="space-y-5" id="npt-registration-form" enctype="multipart/form-data">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Họ tên người phụ thuộc <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" required maxlength="255" value="{{ old('full_name') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                           placeholder="VD: Nguyễn Văn A">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Quan hệ <span class="text-rose-500">*</span></label>
                    <select name="relationship" required x-model="relationship"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach($relationshipLabels as $val => $label)
                            <option value="{{ $val }}" @selected(old('relationship', 'child') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="relationship === 'child'" x-cloak>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Loại con <span class="text-rose-500">*</span></label>
                    <select name="child_category" x-model="childCategory" x-bind:disabled="relationship !== 'child'"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach($childCategoryLabels as $val => $label)
                            <option value="{{ $val }}" @selected(old('child_category', 'minor') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">
                            Ngày sinh
                            <span class="text-rose-500" x-show="relationship === 'child'">*</span>
                        </label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               :required="relationship === 'child'"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số CCCD/CMND (nhập trên giấy tờ)</label>
                        <input type="text" name="id_number" maxlength="30" value="{{ old('id_number') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày bắt đầu giảm trừ <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" required value="{{ old('start_date', now()->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>

                <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-600">Đính kèm giấy tờ (PDF, JPG, PNG · tối đa 5MB)</p>

                    @foreach($documentTypeLabels as $type => $label)
                        <div x-show="needsDoc('{{ $type }}')" x-cloak>
                            <label class="mb-2 block text-xs font-semibold text-slate-700">
                                {{ $label }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" name="document_{{ $type }}" accept=".pdf,.jpg,.jpeg,.png"
                                   :required="needsDoc('{{ $type }}')"
                                   class="w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-violet-800">
                        </div>
                    @endforeach
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ghi chú thêm</label>
                    <textarea name="note" rows="2" maxlength="1000"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                              placeholder="Thông tin bổ sung (nếu có)...">{{ old('note') }}</textarea>
                </div>

                <div class="rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-xs text-amber-900">
                    Yêu cầu và <strong>giấy tờ đính kèm</strong> gửi tới kế toán. Thiếu hoặc không đúng hồ sơ có thể bị từ chối.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" id="npt-submit-btn" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-violet-700">
                        Gửi tới kế toán
                    </button>
                    <a href="{{ route('employee.tax-dependents.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Hủy</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function nptRegistrationForm() {
            const map = @json($requiredTypesMap);

            return {
                relationship: @json(old('relationship', 'child')),
                childCategory: @json(old('child_category', 'minor')),
                map,
                mapKey() {
                    if (this.relationship === 'child') {
                        return 'child|' + this.childCategory;
                    }
                    return this.relationship;
                },
                activeTypes() {
                    return this.map[this.mapKey()] || [];
                },
                needsDoc(type) {
                    return this.activeTypes().includes(type);
                },
            };
        }

        document.getElementById('npt-registration-form')?.addEventListener('submit', function () {
            const btn = document.getElementById('npt-submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Đang gửi...';
            }
        });
    </script>
</x-dynamic-component>
