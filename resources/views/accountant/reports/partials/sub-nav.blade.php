@php
    $items = [
        ['label' => 'Trung tâm', 'route' => 'accountant.reports.index', 'key' => 'hub'],
        ['label' => 'Chi phí lương PB', 'route' => 'accountant.reports.salary-by-department', 'key' => 'salary'],
        ['label' => 'Ngân sách', 'route' => 'accountant.reports.budget-comparison', 'key' => 'budget'],
        ['label' => 'Xuất báo cáo', 'route' => 'accountant.reports.financial', 'key' => 'financial'],
    ];
@endphp

<nav class="mb-5 flex flex-wrap gap-2 rounded-2xl border border-indigo-100/80 bg-white/80 p-2 shadow-sm">
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ ($active ?? '') === $item['key'] ? 'bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow-md' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-800' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
