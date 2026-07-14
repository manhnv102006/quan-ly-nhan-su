<x-admin-layout title="Gia hạn hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Gia hạn hợp đồng</h2>
                <p class="text-sm text-slate-500">
                    Tạo HĐ mới, HĐ cũ <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span> → <strong>Đã thay thế</strong>.
                </p>
            </div>
            <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Quay lại chi tiết</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <div class="mb-5 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm text-slate-700">
                <span class="font-semibold text-slate-800">{{ $contract->employee->full_name ?? 'N/A' }}</span>
                · {{ $contract->contractType->contract_name ?? '—' }}
                · Lần gia hạn hiện tại: <strong>{{ $contract->renewal_count ?? 0 }}</strong>
                · Thời hạn cũ:
                {{ optional($contract->start_date)->format('d/m/Y') }} → {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}
                @if($contract->isExpiringSoon())
                    <span class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">Sắp hết hạn</span>
                @endif
            </div>

            @if($renewalBlocked)
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    <p class="font-semibold">{{ \App\Models\Contract::fixedTermRenewalBlockedMessage() }}</p>
                    <p class="mt-2 text-amber-800">Hợp đồng xác định thời hạn chỉ được gia hạn tối đa 1 lần. Vui lòng dùng chức năng chuyển loại.</p>
                    <a href="{{ route('admin.contracts.convert.form', $contract) }}"
                       class="mt-4 inline-flex items-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                        Chuyển sang Không xác định thời hạn
                    </a>
                </div>
            @else
                @if($errors->has('contract'))
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first('contract') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.contracts.extend', $contract) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label for="contract_code" class="admin-label">Mã hợp đồng mới</label>
                            <input type="text" id="contract_code" name="contract_code" class="admin-field"
                                   value="{{ old('contract_code', $nextCode) }}">
                            @error('contract_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contract_type_id" class="admin-label">Loại hợp đồng *</label>
                            <select id="contract_type_id" name="contract_type_id" class="admin-field" required data-contract-type-select>
                                @foreach($contractTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-internship="{{ $type->isInternship() ? '1' : '0' }}"
                                            @selected(old('contract_type_id', $contract->contract_type_id) == $type->id)>
                                        {{ $type->contract_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('contract_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="start_date" class="admin-label">Ngày bắt đầu HĐ mới *</label>
                            <input type="date" id="start_date" name="start_date" class="admin-field" required
                                   value="{{ old('start_date', $suggestedStart) }}">
                            <p class="mt-1 text-[11px] text-slate-400">Thường = ngày sau ngày kết thúc HĐ cũ.</p>
                            @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="admin-label">Ngày kết thúc HĐ mới *</label>
                            <input type="date" id="end_date" name="end_date" class="admin-field" required
                                   value="{{ old('end_date', $suggestedEnd) }}">
                            @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="salary" class="admin-label">Lương cơ bản *</label>
                            <input type="text" id="salary" name="salary" class="admin-field money-input" inputmode="numeric" required
                                   value="{{ old('salary', (int) $contract->salary) }}" placeholder="VD: 15.000.000">
                            @error('salary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="contract_file" class="admin-label">File hợp đồng mới (tùy chọn)</label>
                            <input type="file" id="contract_file" name="contract_file" class="admin-field" accept=".pdf,.doc,.docx">
                            @error('contract_file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <label for="note" class="admin-label">Ghi chú</label>
                            <textarea id="note" name="note" rows="2" class="admin-field">{{ old('note') }}</textarea>
                            @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <input type="hidden" name="position_id" value="{{ $contract->position_id }}">
                    <select class="hidden" data-position-select>
                        <option value="{{ $contract->position_id }}" selected></option>
                    </select>

                    @include('admin.contracts.partials.allowance-fields', [
                        'allowanceTypes' => $allowanceTypes,
                        'allowanceValues' => $allowanceValues,
                        'positions' => $positions,
                    ])

                    <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Hủy</a>
                        <button type="submit" class="admin-btn-violet px-6">Xác nhận gia hạn</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const moneyInputs = document.querySelectorAll('.money-input');
                function formatMoney(value) {
                    const digits = (value || '').toString().replace(/\D/g, '');
                    if (digits === '') return '';
                    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                moneyInputs.forEach(function (input) {
                    input.value = formatMoney(input.value);
                    input.addEventListener('input', function () { this.value = formatMoney(this.value); });
                    const form = input.closest('form');
                    if (form) {
                        form.addEventListener('submit', function () {
                            input.value = (input.value || '').replace(/\D/g, '');
                        });
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
