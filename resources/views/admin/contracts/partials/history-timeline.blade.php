@if($histories->isNotEmpty())
    <ul class="space-y-3">
        @foreach($histories as $item)
            <li class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $item->action_badge_class }}">
                                {{ $item->action_label }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">
                                {{ $item->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-800">{{ $item->summary }}</p>
                        @if($item->note)
                            <p class="mt-1 text-xs text-slate-500">Ghi chú: {{ $item->note }}</p>
                        @endif
                        @if(! empty($item->changes))
                            <details class="mt-2">
                                <summary class="cursor-pointer text-xs font-semibold text-violet-600">Chi tiết thay đổi</summary>
                                <ul class="mt-2 space-y-1 text-xs text-slate-600">
                                    @foreach($item->changes as $change)
                                        <li>
                                            <span class="font-medium text-slate-700">{{ $change['label'] }}:</span>
                                            {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @elseif(! empty($item->allowances_snapshot))
                            <details class="mt-2" open>
                                <summary class="cursor-pointer text-xs font-semibold text-violet-600">Phụ cấp đã chốt</summary>
                                <ul class="mt-2 space-y-1 text-xs text-slate-600">
                                    @foreach($item->allowances_snapshot as $line)
                                        <li>
                                            <span class="font-medium text-slate-700">{{ $line['label'] ?? 'Phụ cấp' }}:</span>
                                            {{ number_format((float) ($line['amount'] ?? 0), 0, ',', '.') }}₫
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        @if($item->contract)
                            <a href="{{ route('admin.contracts.show', $item->contract_id) }}"
                               class="font-semibold text-violet-600 hover:underline">
                                {{ $item->contract->contract_code }}
                            </a>
                        @endif
                        @if($item->relatedContract)
                            <p class="mt-0.5">
                                →
                                <a href="{{ route('admin.contracts.show', $item->related_contract_id) }}"
                                   class="font-semibold text-violet-600 hover:underline">
                                    {{ $item->relatedContract->contract_code }}
                                </a>
                            </p>
                        @endif
                        <p class="mt-1">{{ $item->performer?->name ?? 'Hệ thống' }}</p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@else
    <p class="py-6 text-center text-sm text-slate-500">Chưa có lịch sử thao tác.</p>
@endif
