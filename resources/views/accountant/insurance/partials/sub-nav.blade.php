@php
    $items = [
        ['label' => 'Hồ sơ BH', 'route' => 'accountant.insurance.index', 'key' => 'profiles'],
        ['label' => 'Báo cáo nộp BH', 'route' => 'accountant.insurance.reports', 'key' => 'reports'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-sky-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-sky-500 to-indigo-500 text-white shadow-md' : 'text-slate-600 hover:bg-sky-50 hover:text-sky-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
