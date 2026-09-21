@php
    use App\Models\LeaveType;

    $leaveType = $leaveType ?? null;
    $isSystem = (bool) ($leaveType->is_system ?? false);
    $currentQuotaType = old('quota_type', $leaveType->quota_type ?? LeaveType::QUOTA_NONE);
    $currentPayer = old('salary_payer', $leaveType->salary_payer ?? LeaveType::PAYER_COMPANY);
    $requiresDocument = (bool) old('requires_document', $leaveType->requires_document ?? false);
    $enforceQuota = (bool) old('enforce_quota', $leaveType->enforce_quota ?? false);
    $quotaDaysUnits = [
        LeaveType::QUOTA_DAYS_PER_YEAR => 'ngày/năm',
        LeaveType::QUOTA_DAYS_PER_EVENT => 'ngày/lần nghỉ',
        LeaveType::QUOTA_TIMES_PER_PREGNANCY => 'lần/thai kỳ',
        LeaveType::QUOTA_MONTHS_PER_EVENT => 'tháng/lần nghỉ',
    ];
@endphp

<div
    x-data="{
        quotaType: @js($currentQuotaType),
        payer: @js($currentPayer),
        requiresDocument: @js($requiresDocument),
        enforceQuota: @js($enforceQuota),
        numericQuotaTypes: @js(LeaveType::NUMERIC_QUOTA_TYPES),
        enforceableQuotaTypes: @js(LeaveType::ENFORCEABLE_QUOTA_TYPES),
        quotaUnits: @js($quotaDaysUnits),
        get needsQuotaNumber() { return this.numericQuotaTypes.includes(this.quotaType); },
        get canEnforce() { return this.enforceableQuotaTypes.includes(this.quotaType); },
        get quotaUnit() { return this.quotaUnits[this.quotaType] ?? ''; },
        get payerHasPercent() { return this.payer === 'company' || this.payer === 'insurance'; },
    }"
    x-effect="if (! canEnforce) enforceQuota = false"
    class="space-y-8"
>
    <style>[x-cloak] { display: none !important; }</style>

    {{-- Thông tin chung --}}
    <section>
        <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin chung</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="name" class="admin-label">Tên loại nghỉ phép *</label>
                <input type="text" id="name" name="name" class="admin-field" required maxlength="100"
                       value="{{ old('name', $leaveType->name ?? '') }}" placeholder="VD: Nghỉ việc riêng">
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="code" class="admin-label">Mã (code) *</label>
                <input type="text" id="code" name="code" required maxlength="50"
                       class="admin-field {{ $isSystem ? 'bg-slate-50' : '' }}"
                       value="{{ old('code', $leaveType->code ?? '') }}"
                       placeholder="vd: nghi_viec_rieng"
                       @if($isSystem) readonly @endif>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $isSystem
                        ? 'Loại hệ thống — không đổi được mã vì các đơn nghỉ cũ đang tham chiếu tới mã này.'
                        : 'Chữ thường, số và dấu gạch dưới. Không đổi sau khi đã có đơn sử dụng.' }}
                </p>
                @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="admin-label">Mô tả</label>
                <textarea id="description" name="description" rows="2" class="admin-field"
                          maxlength="1000">{{ old('description', $leaveType->description ?? '') }}</textarea>
                @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Quy tắc tính --}}
    <section class="border-t border-slate-100 pt-6">
        <h3 class="mb-4 text-sm font-bold text-slate-800">Quy tắc tính</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="annual_deduction" class="admin-label">Có trừ phép năm không? *</label>
                <select id="annual_deduction" name="annual_deduction" class="admin-field" required>
                    @foreach (LeaveType::ANNUAL_DEDUCTION_LABELS as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('annual_deduction', $leaveType->annual_deduction ?? LeaveType::ANNUAL_DEDUCTION_NO) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('annual_deduction')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="salary_payer" class="admin-label">Lương do ai trả? *</label>
                <select id="salary_payer" name="salary_payer" class="admin-field" required x-model="payer">
                    @foreach (LeaveType::PAYER_LABELS as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('salary_payer', $leaveType->salary_payer ?? LeaveType::PAYER_COMPANY) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('salary_payer')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="payerHasPercent" x-cloak>
                <label for="salary_percent" class="admin-label">Tỷ lệ hưởng lương (%)</label>
                <input type="number" id="salary_percent" name="salary_percent" class="admin-field"
                       min="0" max="100" step="0.01"
                       value="{{ old('salary_percent', $leaveType?->salary_percent !== null ? (float) $leaveType->salary_percent : '') }}"
                       placeholder="VD: 75 cho nghỉ ốm hưởng BHXH">
                <p class="mt-1 text-xs text-slate-500">Bỏ trống nếu hưởng nguyên lương. Chỉ áp dụng khi công ty hoặc BHXH chi trả.</p>
                @error('salary_percent')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="gender_restriction" class="admin-label">Giới hạn giới tính</label>
                <select id="gender_restriction" name="gender_restriction" class="admin-field">
                    <option value="">Không giới hạn</option>
                    @foreach (LeaveType::GENDER_LABELS as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('gender_restriction', $leaveType->gender_restriction ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">VD: thai sản chỉ dành cho nữ, nghỉ vợ sinh con chỉ dành cho nam.</p>
                @error('gender_restriction')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Hạn mức --}}
    <section class="border-t border-slate-100 pt-6">
        <h3 class="mb-4 text-sm font-bold text-slate-800">Hạn mức nghỉ</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="quota_type" class="admin-label">Cách áp dụng hạn mức *</label>
                <select id="quota_type" name="quota_type" class="admin-field" required x-model="quotaType">
                    @foreach (LeaveType::QUOTA_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($currentQuotaType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('quota_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="needsQuotaNumber" x-cloak>
                <label for="quota_days" class="admin-label">
                    Hạn mức <span x-text="quotaUnit ? '(' + quotaUnit + ')' : ''"></span> *
                </label>
                <input type="number" id="quota_days" name="quota_days" class="admin-field" min="0.5" max="999" step="0.5"
                       value="{{ old('quota_days', $leaveType?->quota_days !== null ? (float) $leaveType->quota_days : '') }}">
                @error('quota_days')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="quota_note" class="admin-label">Ghi chú hạn mức</label>
                <input type="text" id="quota_note" name="quota_note" class="admin-field" maxlength="255"
                       value="{{ old('quota_note', $leaveType->quota_note ?? '') }}"
                       placeholder="VD: Tối đa 30, 40 hoặc 60 ngày/năm tùy thời gian đóng BHXH">
                <p class="mt-1 text-xs text-slate-500">Hiển thị cho nhân viên thay cho con số hạn mức. Dùng khi hạn mức thay đổi theo từng người.</p>
                @error('quota_note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2" x-show="canEnforce" x-cloak>
                <label class="inline-flex items-start gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="enforce_quota" value="1" class="mt-0.5 rounded border-slate-300 text-violet-600"
                           x-model="enforceQuota" @checked($enforceQuota)>
                    <span>
                        Chặn đơn khi vượt hạn mức
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">
                            Hệ thống từ chối đơn nếu số ngày xin nghỉ vượt hạn mức đã cấu hình (tính cả đơn đang chờ duyệt).
                            Chỉ bật khi hạn mức áp dụng như nhau cho mọi nhân viên.
                        </span>
                    </span>
                </label>
                @error('enforce_quota')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Giấy tờ --}}
    <section class="border-t border-slate-100 pt-6">
        <h3 class="mb-4 text-sm font-bold text-slate-800">Giấy tờ minh chứng</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="inline-flex items-start gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="requires_document" value="1" class="mt-0.5 rounded border-slate-300 text-violet-600"
                           x-model="requiresDocument" @checked($requiresDocument)>
                    <span>
                        Bắt buộc đính kèm giấy tờ
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">
                            Nhân viên phải tải lên tệp PDF/JPG/PNG (tối đa 5MB) khi gửi đơn loại này.
                        </span>
                    </span>
                </label>
            </div>

            <div class="md:col-span-2" x-show="requiresDocument" x-cloak>
                <label for="document_hint" class="admin-label">Giấy tờ cần nộp *</label>
                <input type="text" id="document_hint" name="document_hint" class="admin-field" maxlength="255"
                       value="{{ old('document_hint', $leaveType->document_hint ?? '') }}"
                       placeholder="VD: Giấy đăng ký kết hôn hoặc thiệp cưới.">
                @error('document_hint')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Hiển thị --}}
    <section class="border-t border-slate-100 pt-6">
        <h3 class="mb-4 text-sm font-bold text-slate-800">Hiển thị &amp; trạng thái</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="color" class="admin-label">Màu huy hiệu *</label>
                <select id="color" name="color" class="admin-field" required>
                    @foreach (LeaveType::COLOR_CLASSES as $value => $classes)
                        <option value="{{ $value }}" @selected(old('color', $leaveType->color ?? 'slate') === $value)>
                            {{ LeaveType::COLOR_LABELS[$value] ?? $value }}
                        </option>
                    @endforeach
                </select>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach (LeaveType::COLOR_CLASSES as $value => $classes)
                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $classes }}">
                            {{ LeaveType::COLOR_LABELS[$value] ?? $value }}
                        </span>
                    @endforeach
                </div>
                @error('color')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="sort_order" class="admin-label">Thứ tự hiển thị</label>
                <input type="number" id="sort_order" name="sort_order" class="admin-field" min="0" max="9999"
                       value="{{ old('sort_order', $leaveType->sort_order ?? 0) }}">
                @error('sort_order')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2 space-y-3">
                <label class="inline-flex items-start gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-violet-600"
                           @checked(old('is_active', $leaveType->is_active ?? true))>
                    <span>
                        Đang sử dụng
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">Tắt để ẩn khỏi form tạo đơn; đơn cũ vẫn giữ nguyên loại nghỉ.</span>
                    </span>
                </label>

                <label class="inline-flex items-start gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="counts_as_leave" value="1" class="mt-0.5 rounded border-slate-300 text-violet-600"
                           @checked(old('counts_as_leave', $leaveType->counts_as_leave ?? true))>
                    <span>
                        Tính là ngày nghỉ
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">Bỏ chọn với các trường hợp vắng mặt nhưng vẫn làm việc, ví dụ đi công tác.</span>
                    </span>
                </label>

                <label class="inline-flex items-start gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="auto_generated" value="1" class="mt-0.5 rounded border-slate-300 text-violet-600"
                           @checked(old('auto_generated', $leaveType->auto_generated ?? false))>
                    <span>
                        Hệ thống tự sinh
                        <span class="mt-0.5 block text-xs font-normal text-slate-500">
                            Đánh dấu loại nghỉ do hệ thống tạo theo lịch, ví dụ nghỉ lễ, Tết.
                            Loại này <strong>không hiện</strong> trên form tạo đơn của nhân viên.
                        </span>
                    </span>
                </label>
            </div>
        </div>
    </section>
</div>
