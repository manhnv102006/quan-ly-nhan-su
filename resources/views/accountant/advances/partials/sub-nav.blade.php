@php
    $items = [
        ['label' => 'Yêu cầu tạm ứng', 'route' => 'accountant.advances.index', 'key' => 'requests'],
        ['label' => 'Số dư tạm ứng', 'route' => 'accountant.advances.balances', 'key' => 'balances'],
        ['label' => 'Trừ vào lương', 'route' => 'accountant.advances.deduct', 'key' => 'deduct'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-cyan-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white shadow-md' : 'text-slate-600 hover:bg-cyan-50 hover:text-cyan-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
