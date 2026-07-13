@props([
    'contract',
    'allowanceBreakdown',
    'totalAllowance',
    'showEmployee' => true,
    'history' => null,
])

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="admin-card p-5 sm:p-6">
            <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin hợp đồng</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @if($showEmployee)
                    <div>
                        <p class="text-[11px] font-bold uppercase text-slate-400">Nhân viên</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $contract->employee->full_name ?? '—' }}</p>
                    </div>
                @endif
                @foreach ([
                    ['label' => 'Phòng ban', 'value' => $contract->display_department_name],
                    ['label' => 'Chức vụ', 'value' => $contract->display_position_name],
                    ['label' => 'Loại hợp đồng', 'value' => $contract->contractType->contract_name ?? '—'],
                    ['label' => 'Ngày ký', 'value' => optional($contract->signed_date)->format('d/m/Y') ?? '—'],
                    ['label' => 'Ngày bắt đầu', 'value' => optional($contract->start_date)->format('d/m/Y') ?? '—'],
                    ['label' => 'Ngày kết thúc', 'value' => optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định'],
                    ['label' => 'Lương cơ bản', 'value' => number_format($contract->salary, 0, ',', '.') . '₫'],
                    ['label' => 'Tổng phụ cấp', 'value' => number_format($totalAllowance, 0, ',', '.') . '₫'],
                ] as $field)
                    <div>
                        <p class="text-[11px] font-bold uppercase text-slate-400">{{ $field['label'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] }}</p>
                    </div>
                @endforeach
            </div>

            @if($contract->latest_termination)
                <div class="mt-5 rounded-xl border border-rose-100 bg-rose-50/50 px-4 py-3 text-sm">
                    <p class="font-semibold text-rose-800">Chấm dứt: {{ $contract->latest_termination->reason_label }}</p>
                    <p class="text-rose-700">Ngày {{ $contract->latest_termination->end_date?->format('d/m/Y') }}</p>
                    @if($contract->latest_termination->note)
                        <p class="mt-1 text-rose-600">{{ $contract->latest_termination->note }}</p>
                    @endif
                </div>
            @endif

            <div class="mt-5 border-t border-slate-100 pt-5">
                <h4 class="mb-3 text-sm font-bold text-slate-800">Chi tiết phụ cấp</h4>
                <div class="space-y-2">
                    @foreach($allowanceBreakdown as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ $item['name'] }}</span>
                            <span class="font-semibold text-slate-800">{{ number_format($item['amount'], 0, ',', '.') }}₫</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($contract->description || $contract->note)
                <div class="mt-5 grid grid-cols-1 gap-4 border-t border-slate-100 pt-5 text-sm">
                    @if($contract->description)
                        <div><p class="text-[11px] font-bold uppercase text-slate-400">Mô tả</p><p class="mt-1 text-slate-700">{{ $contract->description }}</p></div>
                    @endif
                    @if($contract->note)
                        <div><p class="text-[11px] font-bold uppercase text-slate-400">Ghi chú</p><p class="mt-1 text-slate-700">{{ $contract->note }}</p></div>
                    @endif
                </div>
            @endif
        </div>

        @if($history && $history->isNotEmpty())
            <div class="admin-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Lịch sử hợp đồng</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-5 py-3">Mã</th>
                                <th class="px-5 py-3">Bắt đầu</th>
                                <th class="px-5 py-3">Kết thúc</th>
                                <th class="px-5 py-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($history as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium">{{ $item->contract_code }}</td>
                                    <td class="px-5 py-3">{{ optional($item->start_date)->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3">{{ optional($item->end_date)->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-5 py-3">@include('admin.contracts.partials.status-badge', ['contract' => $item])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
