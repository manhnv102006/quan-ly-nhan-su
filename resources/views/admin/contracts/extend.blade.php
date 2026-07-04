<x-admin-layout title="Gia hạn hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Gia hạn hợp đồng</h2>
                <p class="text-sm text-slate-500">
                    Tạo hợp đồng mới và đánh dấu hợp đồng cũ <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span> hết hiệu lực.
                </p>
            </div>
            <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Quay lại chi tiết</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <div class="mb-5 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm text-slate-700">
                <span class="font-semibold text-slate-800">{{ $contract->employee->full_name ?? 'N/A' }}</span>
                · {{ $contract->contractType->contract_name ?? '—' }}
                · Thời hạn cũ:
                {{ optional($contract->start_date)->format('d/m/Y') }} → {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}
            </div>

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
                        <select id="contract_type_id" name="contract_type_id" class="admin-field" required>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('contract_type_id', $contract->contract_type_id) == $type->id)>
                                    {{ $type->contract_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contract_type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="start_date" class="admin-label">Ngày bắt đầu *</label>
                        <input type="date" id="start_date" name="start_date" class="admin-field" required
                               value="{{ old('start_date', optional($contract->end_date)->format('Y-m-d')) }}">
                        @error('start_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="end_date" class="admin-label">Ngày kết thúc *</label>
                        <input type="date" id="end_date" name="end_date" class="admin-field" required value="{{ old('end_date') }}">
                        @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="salary" class="admin-label">Lương cơ bản *</label>
                        <input type="text" id="salary" name="salary" class="admin-field money-input" inputmode="numeric" required
                               value="{{ old('salary', (int) $contract->salary) }}" placeholder="VD: 15.000.000">
                        @error('salary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="allowance" class="admin-label">Phụ cấp</label>
                        <input type="number" id="allowance" name="allowance" class="admin-field" min="0"
                               value="{{ old('allowance', $contract->allowance) }}">
                        @error('allowance')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
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

                <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                    <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Hủy</a>
                    <button type="submit" class="admin-btn-violet px-6">Lưu hợp đồng mới</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Định dạng ô tiền: phân tách hàng nghìn bằng dấu chấm (VD: 15.000.000).
                const moneyInputs = document.querySelectorAll('.money-input');

                function formatMoney(value) {
                    const digits = (value || '').toString().replace(/\D/g, '');
                    if (digits === '') return '';
                    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }

                moneyInputs.forEach(function (input) {
                    input.value = formatMoney(input.value);

                    input.addEventListener('input', function () {
                        this.value = formatMoney(this.value);
                    });

                    const form = input.closest('form');
                    if (form) {
                        // Bỏ dấu chấm trước khi gửi để server nhận số thuần.
                        form.addEventListener('submit', function () {
                            input.value = (input.value || '').replace(/\D/g, '');
                        });
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
