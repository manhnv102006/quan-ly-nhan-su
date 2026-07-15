@php
    $navigation = $navigation ?? \App\Support\AccountantNavigation::items();
    $groupedNavigation = collect($navigation)->groupBy(fn ($item) => $item['group'] ?? \App\Support\AccountantNavigation::GROUP_FINANCE);
    $groupLabels = \App\Support\AccountantNavigation::groupLabels();
@endphp

@foreach ($groupLabels as $groupKey => $groupLabel)
    @if ($groupedNavigation->has($groupKey))
        <div class="{{ $loop->first ? '' : 'mt-5' }}">
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ $groupLabel }}</p>
            <div class="space-y-1">
                @foreach ($groupedNavigation[$groupKey] as $item)
                    @php
                        $isActive = isset($item['route']) ? request()->routeIs($item['route']) : false;
                    @endphp
                    <a href="{{ $item['href'] }}"
                       class="accountant-menu-item {{ $isActive ? 'accountant-menu-item-active' : 'accountant-menu-item-inactive' }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 shadow-sm' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate">{{ $item['label'] }}</span>
                            @if (! empty($item['note']))
                                <span class="block truncate text-[11px] {{ $isActive ? 'text-white/75' : 'text-slate-400' }}">{{ $item['note'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
