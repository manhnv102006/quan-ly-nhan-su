<x-admin-layout title="Chi tiết hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-800">Chi tiết hợp đồng</h2>
                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                    @if($contract->isExpiringSoon())
                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-700">Sắp hết hạn</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500">
                    Mã <span class="font-semibold text-violet-600">{{ $contract->contract_code }}</span>
                    · {{ $contract->employee->full_name ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Danh sách</a>
                @if($contract->isEditable())
                    <a href="{{ route('admin.contracts.edit', $contract) }}" class="admin-btn-secondary">Sửa</a>
                @endif
                @if($contract->canBeExtended() && ! $contract->isFixedTermRenewalBlocked())
                    <a href="{{ route('admin.contracts.extend.form', $contract) }}" class="admin-btn-violet">Gia hạn</a>
                @endif
                @if($contract->canBeExtended() || $contract->isEditable())
                    <a href="{{ route('admin.contracts.convert.form', $contract) }}" class="admin-btn-secondary">Chuyển loại HĐ</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Thông tin chính --}}
            <div class="space-y-6 xl:col-span-2">
                <div class="admin-card p-5 sm:p-6">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin hợp đồng</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['label' => 'Nhân viên', 'value' => $contract->employee->full_name ?? '—'],
                            ['label' => 'Mã nhân viên', 'value' => $contract->employee->employee_code ?? '—'],
                            ['label' => 'Phòng ban', 'value' => $contract->display_department_name],
                            ['label' => 'Chức vụ', 'value' => $contract->display_position_name],
                            ['label' => 'Loại hợp đồng', 'value' => $contract->contractType->contract_name ?? '—'],
                            ['label' => 'Người tạo', 'value' => $contract->creator->name ?? '—'],
                            ['label' => 'Ngày bắt đầu', 'value' => optional($contract->start_date)->format('d/m/Y') ?? '—'],
                            ['label' => 'Ngày kết thúc', 'value' => optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định'],
                            ['label' => 'Ngày ký', 'value' => optional($contract->signed_date)->format('d/m/Y') ?? '—'],
                            ['label' => 'Lương cơ bản', 'value' => number_format($contract->salary, 0, ',', '.') . '₫'],
                            ['label' => 'Tổng phụ cấp', 'value' => number_format($totalAllowance, 0, ',', '.') . '₫'],
                        ] as $field)
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $field['label'] }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <h4 class="mb-3 text-sm font-bold text-slate-800">Chi tiết phụ cấp</h4>
                        <div class="overflow-x-auto rounded-xl border border-slate-100">
                            <table class="w-full min-w-[480px] text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                                        <th class="px-4 py-3">Loại phụ cấp</th>
                                        <th class="px-4 py-3">Ghi chú</th>
                                        <th class="px-4 py-3 text-right">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($allowanceBreakdown as $item)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $item['name'] }}</td>
                                            <td class="px-4 py-3 text-slate-500">{{ $item['note'] ?: '—' }}</td>
                                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                                {{ number_format($item['amount'], 0, ',', '.') }}₫
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-violet-50/60">
                                        <td colspan="2" class="px-4 py-3 text-sm font-bold text-violet-800">Tổng phụ cấp</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-violet-800">
                                            {{ number_format($totalAllowance, 0, ',', '.') }}₫
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 border-t border-slate-100 pt-5">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Mô tả</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $contract->description ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Ghi chú</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $contract->note ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">File hợp đồng</p>
                            @if($contract->file_path)
                                <a href="{{ Storage::url($contract->file_path) }}" target="_blank"
                                   class="mt-2 inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12M12 16.5V3" />
                                    </svg>
                                    Tải xuống / Xem
                                </a>
                            @else
                                <p class="mt-1 text-sm text-slate-400">Chưa có tệp đính kèm</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Lịch sử --}}
                <div class="admin-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h3 class="text-sm font-bold text-slate-800">Lịch sử hợp đồng của nhân viên</h3>
                        <p class="text-xs text-slate-500">Các hợp đồng trước và sau của {{ $contract->employee->full_name ?? 'nhân viên' }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px]">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Bắt đầu</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Kết thúc</th>
                                    <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($history as $item)
                                    <tr class="{{ $item->id === $contract->id ? 'bg-violet-50/60' : 'hover:bg-slate-50/60' }}">
                                        <td class="px-5 py-3 text-sm font-semibold text-slate-800">
                                            {{ $item->contract_code }}
                                            @if($item->id === $contract->id)
                                                <span class="ml-1 text-[10px] font-bold text-violet-600">(hiện tại)</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-sm text-slate-700">{{ optional($item->start_date)->format('d/m/Y') }}</td>
                                        <td class="px-5 py-3 text-sm text-slate-700">{{ optional($item->end_date)->format('d/m/Y') ?? '—' }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @include('admin.contracts.partials.status-badge', ['contract' => $item])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">Chưa có lịch sử.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Lịch sử thao tác --}}
                <div class="admin-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Lịch sử thao tác hợp đồng</h3>
                            <p class="text-xs text-slate-500">Ai thêm, sửa, gia hạn, chuyển loại, hủy hoặc chấm dứt hợp đồng</p>
                        </div>
                        <a href="{{ route('admin.contracts.history', ['employee_id' => $contract->employee_id]) }}"
                           class="text-xs font-semibold text-violet-600 hover:underline">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="p-5 sm:p-6">
                        @include('admin.contracts.partials.history-timeline', ['histories' => $activityHistories])
                    </div>
                </div>
            </div>

            {{-- Hành động --}}
            <div class="space-y-6">
                <div class="admin-card p-5 sm:p-6">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">Hành động</h3>

                    @if($contract->status === \App\Models\Contract::STATUS_DRAFT)
                        <form method="POST" action="{{ route('admin.contracts.activate', $contract) }}" class="mb-3">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Kích hoạt hợp đồng này? Hợp đồng active cũ (nếu có) sẽ hết hiệu lực.')"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700">
                                Kích hoạt hợp đồng
                            </button>
                        </form>
                    @endif

                @if($contract->canBeExtended())
                        <div class="space-y-3">
                            @if($contract->isFixedTermRenewalBlocked())
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    <p class="font-semibold">{{ \App\Models\Contract::fixedTermRenewalBlockedMessage() }}</p>
                                    <a href="{{ route('admin.contracts.convert.form', $contract) }}"
                                       class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                                        Chuyển loại HĐ
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('admin.contracts.extend.form', $contract) }}"
                                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Gia hạn hợp đồng
                                </a>
                            @endif
                            <a href="{{ route('admin.contracts.convert.form', $contract) }}"
                               class="flex w-full items-center justify-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                                Chuyển loại (VD: thử việc → chính thức)
                            </a>

                            <details class="rounded-xl border border-rose-100 bg-rose-50/50">
                                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-rose-700">
                                    Chấm dứt hợp đồng
                                </summary>
                                <form method="POST" action="{{ route('admin.contracts.terminate', $contract) }}" class="space-y-3 border-t border-rose-100 px-4 py-4">
                                    @csrf
                                    <div>
                                        <label for="terminate_reason" class="admin-label">Lý do chấm dứt *</label>
                                        <select id="terminate_reason" name="reason" class="admin-field" required>
                                            <option value="">— Chọn lý do —</option>
                                            @foreach(\App\Models\ContractTermination::REASON_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="terminate_end_date" class="admin-label">Ngày chấm dứt *</label>
                                        <input type="date" id="terminate_end_date" name="end_date" class="admin-field" required
                                               value="{{ old('end_date', now()->format('Y-m-d')) }}">
                                        @error('end_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="terminate_note" class="admin-label">Ghi chú</label>
                                        <textarea id="terminate_note" name="note" rows="2" class="admin-field">{{ old('note') }}</textarea>
                                    </div>
                                    <button type="submit"
                                            onclick="return confirm('Xác nhận chấm dứt hợp đồng này?')"
                                            class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                        Xác nhận chấm dứt
                                    </button>
                                </form>
                            </details>
                        </div>
                    @elseif($contract->isEditable())
                        <p class="text-sm text-slate-500">Hợp đồng chưa hiệu lực — chỉ sửa hoặc kích hoạt.</p>
                    @else
                        <p class="text-sm text-slate-500">Hợp đồng không ở trạng thái cho phép thao tác.</p>
                    @endif

                    @if(session('error'))
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ session('error') }}
                            @if(session('suggest_convert') && $contract->canBeExtended())
                                <a href="{{ route('admin.contracts.convert.form', $contract) }}"
                                   class="mt-2 inline-flex text-sm font-semibold text-violet-700 underline">
                                    → Chuyển loại HĐ
                                </a>
                            @endif
                        </div>
                    @endif

                    @if($contract->isDeletable())
                        <form method="POST" action="{{ route('admin.contracts.destroy', $contract) }}" class="mt-4"
                              onsubmit="return confirm('Chuyển hợp đồng vào thùng rác?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn-secondary w-full justify-center text-rose-600 hover:bg-rose-50">
                                Xóa mềm (thùng rác)
                            </button>
                        </form>
                    @endif
                </div>

                @if($contract->trashed())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Hợp đồng này đang nằm trong thùng rác.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
