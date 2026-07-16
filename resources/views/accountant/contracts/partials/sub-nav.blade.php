@php
    $items = [
        ['label' => 'Theo phòng ban', 'route' => 'accountant.contracts.index', 'key' => 'departments'],
        ['label' => 'Lương & phụ cấp', 'route' => 'accountant.contracts.salary-overview', 'key' => 'salary'],
        ['label' => 'Sắp hết hạn', 'route' => 'accountant.contracts.expiring', 'key' => 'expiring'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-rose-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-md' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
