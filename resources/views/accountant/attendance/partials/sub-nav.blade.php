@php
    $items = [
        ['label' => 'Theo phòng ban', 'route' => 'accountant.attendance.index', 'key' => 'departments'],
        ['label' => 'Bảng công tháng', 'route' => 'accountant.attendance.timesheet', 'key' => 'timesheet'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-emerald-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
