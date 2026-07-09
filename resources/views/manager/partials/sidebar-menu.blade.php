@php
    $navigation = $navigation ?? \App\Support\ManagerNavigation::items();
    $user = Auth::user();

    if ($user) {
        $navigation = app(\App\Services\ManagerPendingApprovalService::class)->applyBadgesToNavigation($navigation, $user);
    }

    $groupedNavigation = collect($navigation)->groupBy(fn ($item) => $item['group'] ?? \App\Support\ManagerNavigation::GROUP_OPERATIONS);
    $groupLabels = \App\Support\ManagerNavigation::groupLabels();
@endphp

@foreach ($groupLabels as $groupKey => $groupLabel)
    @if ($groupedNavigation->has($groupKey))
        <div class="{{ $loop->first ? '' : 'mt-5' }}">
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ $groupLabel }}</p>
            <div class="space-y-1">
                @foreach ($groupedNavigation[$groupKey] as $item)
                    @php
                        $isActive = $item['active'] ?? (isset($item['route']) ? request()->routeIs($item['route']) : false);
                    @endphp
                    <a
                        href="{{ $item['href'] }}"
                        @if (! empty($item['target'])) target="{{ $item['target'] }}" @endif
                        class="manager-menu-item {{ $isActive ? 'manager-menu-item-active' : 'manager-menu-item-inactive' }}"
                    >
                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-white/20 text-white' : 'bg-white text-slate-500 shadow-sm shadow-slate-200/60' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            @if (! empty($item['badge']) && $item['badge'] > 0)
                                <x-nav-badge :count="$item['badge']" :active="$isActive" dot-only active-ring="ring-teal-500" />
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="block truncate">{{ $item['label'] }}</span>
                                @if (! empty($item['badge']) && $item['badge'] > 0)
                                    <x-nav-badge :count="$item['badge']" :active="$isActive" active-ring="ring-teal-500" />
                                @endif
                            </span>
                            @if (! empty($item['note']))
                                <span class="block truncate text-[11px] {{ $isActive ? 'text-white/70' : 'text-slate-400' }}">{{ $item['note'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
