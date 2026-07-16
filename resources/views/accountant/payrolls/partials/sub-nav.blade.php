@php
    $items = [
        ['label' => 'Tổng quan', 'route' => 'accountant.payrolls.index', 'key' => 'hub'],
        ['label' => 'Kỳ lương', 'route' => 'accountant.payroll-periods.index', 'key' => 'periods'],
        ['label' => 'Bảng lương', 'route' => 'accountant.payrolls.slips', 'key' => 'slips'],
        ['label' => 'Lịch sử lương', 'route' => 'accountant.payrolls.salary-history', 'key' => 'history'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-amber-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md' : 'text-slate-600 hover:bg-amber-50 hover:text-amber-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
