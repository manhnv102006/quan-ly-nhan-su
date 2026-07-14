<x-admin-layout title="Chuyển loại hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chuyển loại hợp đồng</h2>
                <p class="text-sm text-slate-500">
                    HĐ cũ <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span> → <strong>Đã thay thế</strong>, tạo HĐ mới loại khác.
                </p>
            </div>
            <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <div class="mb-5 rounded-xl border border-violet-100 bg-violet-50/50 px-4 py-3 text-sm text-violet-900">
                <p>Loại hiện tại: <strong>{{ $contract->contractType->contract_name ?? '—' }}</strong>
                    ({{ $contract->contractType->category_label ?? '—' }})</p>
                <p class="mt-1">{{ $contract->employee->full_name ?? 'N/A' }} · Lần gia hạn: {{ $contract->renewal_count ?? 0 }}</p>
            </div>

            <div class="mb-5 rounded-xl border border-sky-100 bg-sky-50/60 px-4 py-3 text-sm text-sky-900">
                <p class="font-semibold">Quy tắc chuyển loại</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Loại đích phải <strong>khác</strong> loại nguồn.</li>
                    <li><strong>Thử việc</strong> → chỉ sang Xác định thời hạn / Không xác định thời hạn.</li>
                    <li><strong>Xác định thời hạn</strong> → sang Không xác định thời hạn: luôn được.</li>
                    <li><strong>Xác định thời hạn</strong> → sang Xác định thời hạn khác: chỉ khi <code>renewal_count = 0</code>.</li>
                    <li>HĐ mới: <code>renewal_count = 0</code>, trạng thái <strong>Còn hiệu lực</strong>.</li>
                </ul>
            </div>

            @if($contractTypes->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    Không có loại hợp đồng đích hợp lệ cho HĐ hiện tại.
                </div>
            @else
                @if($errors->has('contract') || $errors->has('contract_type_id'))
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first('contract') ?: $errors->first('contract_type_id') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.contracts.convert', $contract) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label for="contract_code" class="admin-label">Mã hợp đồng mới</label>
                            <input type="text" id="contract_code" name="contract_code" class="admin-field" value="{{ old('contract_code', $nextCode) }}">
                        </div>
                        <div>
                            <label for="contract_type_id" class="admin-label">Loại hợp đồng đích *</label>
                            <select id="contract_type_id" name="contract_type_id" class="admin-field" required data-contract-type-select>
                                <option value="">— Chọn loại mới —</option>
                                @foreach($contractTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-internship="{{ $type->isInternship() ? '1' : '0' }}"
                                            data-indefinite="{{ $type->isIndefinite() ? '1' : '0' }}"
                                            @selected(old('contract_type_id') == $type->id)>
                                        {{ $type->contract_name }} ({{ $type->category_label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('contract_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="effective_date" class="admin-label">Ngày hiệu lực chuyển đổi *</label>
                            <input type="date" id="effective_date" name="effective_date" class="admin-field" required
                                   value="{{ old('effective_date', now()->format('Y-m-d')) }}">
                            <p class="mt-1 text-[11px] text-slate-400">HĐ cũ kết thúc vào ngày này.</p>
                            @error('effective_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="start_date" class="admin-label">Ngày bắt đầu HĐ mới *</label>
                            <input type="date" id="start_date" name="start_date" class="admin-field" required
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}">
                            @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="admin-label">Ngày kết thúc HĐ mới</label>
                            <input type="date" id="end_date" name="end_date" class="admin-field" value="{{ old('end_date') }}">
                            <p class="mt-1 text-[11px] text-slate-400">Để trống nếu chọn Không xác định thời hạn.</p>
                            @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="salary" class="admin-label">Lương cơ bản *</label>
                            <input type="text" id="salary" name="salary" class="admin-field money-input" required
                                   value="{{ old('salary', (int) $contract->salary) }}">
                            @error('salary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="contract_file" class="admin-label">File HĐ mới</label>
                            <input type="file" id="contract_file" name="contract_file" class="admin-field" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <label for="note" class="admin-label">Ghi chú</label>
                            <textarea id="note" name="note" rows="2" class="admin-field" placeholder="VD: Chuyển chính thức sau thử việc">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    <input type="hidden" name="position_id" value="{{ $contract->position_id }}">
                    <select class="hidden" data-position-select><option value="{{ $contract->position_id }}" selected></option></select>

                    @include('admin.contracts.partials.allowance-fields', [
                        'allowanceTypes' => $allowanceTypes,
                        'allowanceValues' => $allowanceValues,
                        'positions' => $positions,
                    ])

                    <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Hủy</a>
                        <button type="submit" class="admin-btn-violet px-6">Xác nhận chuyển loại</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const effectiveInput = document.getElementById('effective_date');
                const startInput = document.getElementById('start_date');
                if (effectiveInput && startInput) {
                    effectiveInput.addEventListener('change', function () {
                        startInput.value = this.value;
                    });
                }

                const typeSelect = document.getElementById('contract_type_id');
                const endInput = document.getElementById('end_date');
                if (typeSelect && endInput) {
                    function syncEndRequired() {
                        const opt = typeSelect.selectedOptions[0];
                        const indefinite = opt && opt.dataset.indefinite === '1';
                        endInput.required = !indefinite;
                        if (indefinite) endInput.value = '';
                    }
                    typeSelect.addEventListener('change', syncEndRequired);
                    syncEndRequired();
                }
            });
        </script>
    @endpush
</x-admin-layout>
