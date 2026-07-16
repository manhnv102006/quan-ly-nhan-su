@php
    $items = [
        ['label' => 'Tính thuế', 'route' => 'accountant.tax.index', 'key' => 'calc'],
        ['label' => 'Người phụ thuộc', 'route' => 'accountant.tax.dependents', 'key' => 'dependents'],
        ['label' => 'Tờ khai thuế', 'route' => 'accountant.tax.declaration', 'key' => 'declaration'],
        ['label' => 'Quyết toán năm', 'route' => 'accountant.tax.settlement', 'key' => 'settlement'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-violet-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-md' : 'text-slate-600 hover:bg-violet-50 hover:text-violet-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
