@php
    use App\Support\AccountantNavigation;

    $navigation = $navigation ?? AccountantNavigation::items();
    $user = Auth::user();

    if ($user) {
        $navigation = app(\App\Services\AccountantPendingActionService::class)->applyBadgesToNavigation($navigation, $user);
    }

    $groupedNavigation = collect($navigation)->groupBy(fn ($item) => $item['group'] ?? AccountantNavigation::GROUP_FINANCE);
    $groupLabels = AccountantNavigation::groupLabels();

    $defaultOpenMenu = null;

    foreach ($navigation as $menuItem) {
        if (empty($menuItem['children']) || empty($menuItem['key'])) {
            continue;
        }

        $parentActive = AccountantNavigation::isRouteActive($menuItem['match'] ?? null);
        $childActive = collect($menuItem['children'])->contains(
            fn ($child) => AccountantNavigation::isChildActive($child)
        );

        if ($parentActive || $childActive) {
            $defaultOpenMenu = $menuItem['key'];
            break;
        }
    }
@endphp

<div x-data="{ openMenu: @js($defaultOpenMenu) }">
    @foreach ($groupLabels as $groupKey => $groupLabel)
        @if ($groupedNavigation->has($groupKey))
            <div class="{{ $loop->first ? '' : 'mt-3' }}">
                <p class="mb-1.5 px-2.5 text-[9px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ $groupLabel }}</p>
                <div class="space-y-0.5">
                    @foreach ($groupedNavigation[$groupKey] as $item)
                        @php
                            $hasChildren = ! empty($item['children']);
                            $menuKey = $item['key'] ?? null;
                            $isActive = isset($item['route'])
                                ? request()->routeIs($item['route'])
                                : AccountantNavigation::isRouteActive($item['match'] ?? null);
                            $hasActiveChild = $hasChildren && collect($item['children'])->contains(
                                fn ($child) => AccountantNavigation::isChildActive($child)
                            );
                            $parentHighlighted = $isActive || $hasActiveChild;
                        @endphp

                        @if ($hasChildren && $menuKey)
                            <div>
                                <button
                                    type="button"
                                    @click="openMenu = openMenu === @js($menuKey) ? null : @js($menuKey)"
                                    :aria-expanded="openMenu === @js($menuKey)"
                                    class="accountant-menu-item group w-full text-left {{ $parentHighlighted ? 'accountant-menu-item-active' : 'accountant-menu-item-inactive' }}"
                                >
                                    <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $parentHighlighted ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 shadow-sm' }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="block truncate">{{ $item['label'] }}</span>
                                            <svg
                                                class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 {{ $parentHighlighted ? 'text-white/90' : 'text-slate-400' }}"
                                                :class="openMenu === @js($menuKey) ? 'rotate-90' : ''"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="2"
                                                stroke="currentColor"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </span>
                                    </span>
                                </button>

                                <div
                                    x-show="openMenu === @js($menuKey)"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="ml-4 mt-0.5 space-y-0.5 border-l border-amber-200/80 pl-3"
                                    style="display: none;"
                                >
                                    @foreach ($item['children'] as $child)
                                        @php $childActive = AccountantNavigation::isChildActive($child); @endphp
                                        <a href="{{ $child['href'] }}"
                                           class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ $childActive ? 'bg-amber-50 text-amber-800' : 'text-slate-600 hover:bg-amber-50/80 hover:text-amber-800' }}">
                                            <span class="truncate">{{ $child['label'] }}</span>
                                            @if (! empty($child['badge']) && $child['badge'] > 0)
                                                <x-nav-badge :count="$child['badge']" :active="$childActive" active-ring="ring-amber-500" />
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @elseif (! empty($item['href']))
                            <a href="{{ $item['href'] }}"
                               class="accountant-menu-item {{ $isActive ? 'accountant-menu-item-active' : 'accountant-menu-item-inactive' }}">
                                <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 shadow-sm' }}">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                    </svg>
                                    @if (! empty($item['badge']) && $item['badge'] > 0)
                                        <x-nav-badge :count="$item['badge']" :active="$isActive" dot-only active-ring="ring-amber-500" />
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
