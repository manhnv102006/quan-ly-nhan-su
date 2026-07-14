@props(['stats'])

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Tổng hợp đồng', 'value' => $stats['total'], 'tone' => 'text-slate-800', 'badge' => 'All'],
        ['label' => 'Đang hiệu lực', 'value' => $stats['active'], 'tone' => 'text-emerald-600', 'badge' => 'Active'],
        ['label' => 'Hết hiệu lực', 'value' => $stats['expired'], 'tone' => 'text-amber-600', 'badge' => 'Expired'],
        ['label' => 'Sắp hết hạn', 'value' => $stats['expiring_soon'], 'tone' => 'text-violet-600', 'badge' => '30d'],
    ] as $card)
        <div class="admin-stat-card border border-slate-100 bg-white/90">
            <div class="flex items-start justify-between">
                <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-600">{{ $card['badge'] }}</span>
            </div>
            <p class="mt-2 text-2xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p>
        </div>
    @endforeach
</div>
