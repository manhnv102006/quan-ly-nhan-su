@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Chi tiết hợp đồng" subtitle="{{ $contract->contract_code }}">
    @include('accountant.contracts.partials.sub-nav', ['active' => ''])

    <div class="accountant-page">
        <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            Chế độ chỉ xem — thông tin lương và phụ cấp phục vụ tính lương, bảo hiểm, thuế.
        </div>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-900">Chi tiết hợp đồng</h2>
                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                    @if($contract->isExpiringSoon())
                        <span class="accountant-badge bg-rose-100 text-rose-700">Sắp hết hạn</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500">
                    Mã <span class="font-semibold text-amber-800">{{ $contract->contract_code }}</span>
                    · {{ $contract->employee->full_name ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accountant.contracts.index', ['employee_id' => $contract->employee_id]) }}" class="accountant-btn-secondary">← Hợp đồng NV</a>
                <a href="{{ route('accountant.contracts.index') }}" class="accountant-btn-secondary">Phòng ban</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="accountant-card p-5 sm:p-6">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin hợp đồng</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['label' => 'Nhân viên', 'value' => $contract->employee->full_name ?? '—'],
                            ['label' => 'Mã nhân viên', 'value' => $contract->employee->employee_code ?? '—'],
                            ['label' => 'Phòng ban', 'value' => $contract->display_department_name],
                            ['label' => 'Chức vụ', 'value' => $contract->display_position_name],
                            ['label' => 'Loại hợp đồng', 'value' => $contract->contractType->contract_name ?? '—'],
                            ['label' => 'Ngày bắt đầu', 'value' => optional($contract->start_date)->format('d/m/Y') ?? '—'],
                            ['label' => 'Ngày kết thúc', 'value' => optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định'],
                            ['label' => 'Ngày ký', 'value' => optional($contract->signed_date)->format('d/m/Y') ?? '—'],
                            ['label' => 'Lương cơ bản', 'value' => $formatMoney($contract->salary)],
                            ['label' => 'Tổng phụ cấp', 'value' => $formatMoney($totalAllowance)],
                        ] as $field)
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $field['label'] }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($allowanceBreakdown))
                        <div class="mt-5 border-t border-amber-100/80 pt-5">
                            <h4 class="mb-3 text-sm font-bold text-slate-800">Chi tiết phụ cấp</h4>
                            <div class="overflow-x-auto rounded-xl border border-amber-100/80">
                                <table class="w-full min-w-[480px] text-sm">
                                    <thead>
                                        <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                                            <th class="px-4 py-3">Loại phụ cấp</th>
                                            <th class="px-4 py-3">Ghi chú</th>
                                            <th class="px-4 py-3 text-right">Số tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($allowanceBreakdown as $item)
                                            <tr>
                                                <td class="px-4 py-3 font-medium">{{ $item['label'] }}</td>
                                                <td class="px-4 py-3 text-slate-500">{{ $item['note'] ?: '—' }}</td>
                                                <td class="px-4 py-3 text-right font-semibold">{{ $formatMoney($item['amount']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-amber-50/60">
                                            <td colspan="2" class="px-4 py-3 text-sm font-bold text-amber-800">Tổng phụ cấp</td>
                                            <td class="px-4 py-3 text-right text-sm font-bold text-amber-800">{{ $formatMoney($totalAllowance) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="mt-5 grid grid-cols-1 gap-4 border-t border-amber-100/80 pt-5">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Mô tả</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $contract->description ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Ghi chú</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $contract->note ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="accountant-card overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 text-white">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-100">Tổng thu nhập HĐ</p>
                        <p class="mt-1 text-2xl font-extrabold">{{ $formatMoney($contract->salary + $totalAllowance) }}</p>
                    </div>
                    <div class="space-y-3 p-5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Lương cơ bản</span>
                            <span class="font-semibold">{{ $formatMoney($contract->salary) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Phụ cấp</span>
                            <span class="font-semibold text-amber-700">{{ $formatMoney($totalAllowance) }}</span>
                        </div>
                    </div>
                </div>

                @if($contract->file_path)
                    <div class="accountant-card p-5">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">File hợp đồng</p>
                        <a href="{{ Storage::url($contract->file_path) }}" target="_blank"
                           class="accountant-btn-primary mt-3 inline-flex w-full justify-center">
                            Tải xuống / Xem
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-accountant-layout>
