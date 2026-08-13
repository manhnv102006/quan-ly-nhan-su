@php
    $rows = isset($histories)
        ? $histories
        : ($requestModel?->histories ?? collect());

    if ($rows instanceof \Illuminate\Support\Collection) {
        $rows = $rows->sortByDesc(fn ($row) => $row->processed_at ?? $row->created_at);
    }

    $title = $title ?? 'Lịch sử xử lý';
    $actionLabels = [
        'submitted' => ['label' => 'Gửi đơn', 'class' => 'bg-sky-50 text-sky-700 border-sky-100'],
        'approved' => ['label' => 'Phê duyệt', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
        'rejected' => ['label' => 'Từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-100'],
    ];
@endphp

<section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm {{ $wrapperClass ?? '' }}">
    <div class="border-b border-slate-100 px-6 py-5">
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-400">Lịch sử</p>
        <h3 class="mt-1 text-lg font-bold text-slate-800">{{ $title }}</h3>
    </div>

    @if($rows->isEmpty())
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-slate-500">Chưa có lịch sử xử lý.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                        <th class="px-6 py-4">Thời gian</th>
                        <th class="px-6 py-4">Hành động</th>
                        <th class="px-6 py-4">Người xử lý</th>
                        <th class="px-6 py-4">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $history)
                        @php
                            $action = $actionLabels[$history->action] ?? ['label' => $history->action, 'class' => 'bg-slate-100 text-slate-600 border-slate-200'];
                            $at = $history->processed_at ?? $history->created_at;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">{{ optional($at)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $action['class'] }}">
                                    {{ $action['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">{{ $history->actor?->name ?? 'Hệ thống' }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ $history->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
