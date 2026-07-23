@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Người phụ thuộc" subtitle="Giảm trừ gia cảnh 4.4 triệu/người/tháng">
<div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Quản lý người phụ thuộc</h2>
            <p class="text-sm text-slate-500">Đăng ký NPT để giảm trừ thuế TNCN khi tính lương</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="accountant-card p-5 xl:col-span-1">
                <h3 class="mb-3 text-sm font-bold text-slate-800">Chọn nhân viên</h3>
                <form method="GET">
                    <select name="employee_id" class="accountant-field" onchange="this.form.submit()">
                        <option value="">-- Chọn --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected($selectedEmployee?->id === $emp->id)>
                                {{ $emp->full_name }} ({{ $emp->active_dependents_count }} NPT)
                            </option>
                        @endforeach
                    </select>
                </form>

                @if($selectedEmployee)
                    <form method="POST" action="{{ route('accountant.tax.profile.update', $selectedEmployee) }}" class="mt-5 space-y-3 border-t border-violet-100 pt-4">
                        @csrf
                        @method('PUT')
                        <h4 class="text-xs font-bold uppercase text-violet-700">Hồ sơ thuế NV</h4>
                        <div>
                            <label class="accountant-label">Mã số thuế</label>
                            <input type="text" name="tax_code" value="{{ old('tax_code', $selectedEmployee->taxProfile?->tax_code) }}" class="accountant-field">
                        </div>
                        <div>
                            <label class="accountant-label">GT bản thân/tháng</label>
                            <input type="number" name="personal_deduction" min="0" step="1000"
                                   value="{{ old('personal_deduction', $selectedEmployee->taxProfile?->personal_deduction ?? 11000000) }}" class="accountant-field">
                        </div>
                        <button type="submit" class="accountant-btn-secondary w-full !text-xs">Lưu hồ sơ thuế</button>
                    </form>
                @endif
            </div>

            <div class="xl:col-span-2 space-y-6">
                @if($selectedEmployee)
                    <div class="accountant-card p-5">
                        <h3 class="mb-4 font-bold text-slate-800">Thêm người phụ thuộc — {{ $selectedEmployee->full_name }}</h3>
                        <form method="POST" action="{{ route('accountant.tax.dependents.store', $selectedEmployee) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            @csrf
                            <div>
                                <label class="accountant-label">Họ tên NPT *</label>
                                <input type="text" name="full_name" required class="accountant-field">
                            </div>
                            <div>
                                <label class="accountant-label">Quan hệ *</label>
                                <select name="relationship" required class="accountant-field">
                                    @foreach(\App\Models\TaxDependent::RELATIONSHIP_LABELS as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="accountant-label">Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="accountant-field">
                            </div>
                            <div>
                                <label class="accountant-label">CCCD/CMND</label>
                                <input type="text" name="id_number" class="accountant-field">
                            </div>
                            <div>
                                <label class="accountant-label">GT/tháng</label>
                                <input type="number" name="monthly_deduction" value="4400000" min="0" class="accountant-field">
                            </div>
                            <div>
                                <label class="accountant-label">Ngày bắt đầu GT *</label>
                                <input type="date" name="start_date" required value="{{ now()->format('Y-m-d') }}" class="accountant-field">
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="accountant-btn-primary">Thêm NPT</button>
                            </div>
                        </form>
                    </div>

                    <div class="accountant-card overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                                    <th class="px-4 py-3">Họ tên</th>
                                    <th class="px-4 py-3">Quan hệ</th>
                                    <th class="px-4 py-3 text-right">GT/tháng</th>
                                    <th class="px-4 py-3">Thời hạn</th>
                                    <th class="px-4 py-3">TT</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($dependents as $dep)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $dep->full_name }}</td>
                                        <td class="px-4 py-3">{{ $dep->relationshipLabel() }}</td>
                                        <td class="px-4 py-3 text-right">{{ $formatMoney($dep->monthly_deduction) }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $dep->start_date?->format('d/m/Y') }} @if($dep->end_date)→ {{ $dep->end_date->format('d/m/Y') }}@endif</td>
                                        <td class="px-4 py-3">
                                            @if($dep->status === \App\Models\TaxDependent::STATUS_PENDING)
                                                <span class="accountant-badge bg-amber-100 text-amber-800">Chờ duyệt</span>
                                            @else
                                                <span class="accountant-badge {{ $dep->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                    {{ $dep->is_active ? 'Hiệu lực' : 'Ngừng' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form method="POST" action="{{ route('accountant.tax.dependents.destroy', [$selectedEmployee, $dep]) }}" onsubmit="return confirm('Xóa NPT này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:underline">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Chưa có người phụ thuộc.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="accountant-card p-10 text-center text-slate-500">Chọn nhân viên để quản lý người phụ thuộc.</div>
                @endif
            </div>
        </div>
    </div>
</x-accountant-layout>
