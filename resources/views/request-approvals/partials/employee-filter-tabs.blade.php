@php
    $filter = $filter ?? 'all';
    $stats = $stats ?? ['total' => 0, 'active' => 0, 'history' => 0];
    $routeName = $routeName ?? 'employee.leave-requests';

    $tabs = [
        ['label' => 'Tất cả', 'value' => $stats['total'], 'filter' => 'all'],
        ['label' => 'Đang xử lý', 'value' => $stats['active'], 'filter' => 'active'],
        ['label' => 'Lịch sử', 'value' => $stats['history'], 'filter' => 'history'],
    ];
@endphp

<div class="grid grid-cols-3 gap-3">
    @foreach ($tabs as $tab)
        <a href="{{ route($routeName, ['filter' => $tab['filter']]) }}"
           class="rounded-2xl border px-4 py-3 transition {{ $filter === $tab['filter'] ? 'border-sky-200 bg-sky-50 shadow-sm' : 'border-slate-100 bg-white hover:border-sky-100' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $tab['label'] }}</p>
            <p class="mt-1 text-2xl font-bold {{ $filter === $tab['filter'] ? 'text-sky-700' : 'text-slate-800' }}">{{ $tab['value'] }}</p>
        </a>
    @endforeach
</div>
