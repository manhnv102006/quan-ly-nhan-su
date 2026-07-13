<x-admin-layout title="Chuyển loại hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chuyển loại hợp đồng</h2>
                <p class="text-sm text-slate-500">
                    VD: <strong>Thử việc → Chính thức</strong>. Tạo HĐ mới, HĐ cũ
                    <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span> hết hiệu lực.
                </p>
            </div>
            <a href="{{ route('admin.contracts.show', $contract) }}" class="admin-btn-secondary">Quay lại</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <div class="mb-5 rounded-xl border border-violet-100 bg-violet-50/50 px-4 py-3 text-sm text-violet-900">
                Hiện tại: <strong>{{ $contract->contractType->contract_name ?? '—' }}</strong>
                · {{ $contract->employee->full_name ?? 'N/A' }}
            </div>

            <form method="POST" action="{{ route('admin.contracts.convert', $contract) }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label for="contract_code" class="admin-label">Mã hợp đồng mới</label>
                        <input type="text" id="contract_code" name="contract_code" class="admin-field" value="{{ old('contract_code', $nextCode) }}">
                    </div>
                    <div>
                        <label for="contract_type_id" class="admin-label">Loại hợp đồng mới *</label>
                        <select id="contract_type_id" name="contract_type_id" class="admin-field" required data-contract-type-select>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}"
                                        data-internship="{{ $type->isInternship() ? '1' : '0' }}"
                                        @selected(old('contract_type_id') == $type->id)>
                                    {{ $type->contract_name }} ({{ $type->category_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="admin-label">Ngày bắt đầu *</label>
                        <input type="date" id="start_date" name="start_date" class="admin-field" required
                               value="{{ old('start_date', optional($contract->end_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label for="end_date" class="admin-label">Ngày kết thúc</label>
                        <input type="date" id="end_date" name="end_date" class="admin-field" value="{{ old('end_date') }}">
                    </div>
                    <div>
                        <label for="salary" class="admin-label">Lương cơ bản *</label>
                        <input type="text" id="salary" name="salary" class="admin-field money-input" required
                               value="{{ old('salary', (int) $contract->salary) }}">
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
                    <button type="submit" class="admin-btn-violet px-6">Tạo HĐ mới</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
