@php
    $summaryCards = [
        ['label' => 'Tổng ngày', 'value' => $summary['total'], 'tone' => 'text-slate-800', 'bg' => 'bg-slate-50'],
        ['label' => 'Lần check-in', 'value' => $summary['check_ins'], 'tone' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
        ['label' => 'Lần check-out', 'value' => $summary['check_outs'], 'tone' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
        ['label' => 'Thiếu check-out', 'value' => $summary['missing_checkouts'], 'tone' => 'text-rose-600', 'bg' => 'bg-rose-50'],
        ['label' => 'Đi làm', 'value' => $summary['present'], 'tone' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
        ['label' => 'Đi muộn', 'value' => $summary['late'], 'tone' => 'text-amber-600', 'bg' => 'bg-amber-50'],
        ['label' => 'Vắng', 'value' => $summary['absent'], 'tone' => 'text-rose-600', 'bg' => 'bg-rose-50'],
        ['label' => 'Tổng giờ', 'value' => $summary['total_hours'].'h', 'tone' => 'text-violet-700', 'bg' => 'bg-violet-50'],
    ];
@endphp

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-8">
    @foreach ($summaryCards as $card)
        <div class="rounded-2xl border border-slate-100 {{ $card['bg'] }} p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
            <p class="mt-1.5 text-xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>
