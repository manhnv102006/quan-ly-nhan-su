@php
    $navigation = $navigation ?? \App\Support\EmployeeNavigation::items();
    $user = Auth::user();

    if ($user) {
        $navigation = app(\App\Services\EmployeePendingActionService::class)->applyBadgesToNavigation($navigation, $user);
    }

    $isNavActive = function (array $item): bool {
        if (array_key_exists('active', $item)) {
            return (bool) $item['active'];
        }

        return isset($item['route']) && request()->routeIs($item['route']);
    };

    $defaultOpenMenu = null;

    foreach ($navigation as $menuItem) {
        if (empty($menuItem['children']) || empty($menuItem['key'])) {
            continue;
        }

        $groupActive = collect($menuItem['children'])->contains(fn ($child) => $isNavActive($child));

        if ($groupActive) {
            $defaultOpenMenu = $menuItem['key'];
            break;
        }
    }
@endphp

<div x-data="{ openMenu: @js($defaultOpenMenu) }">
    @foreach ($navigation as $item)
        @php
            $hasChildren = ! empty($item['children']);
            $isActive = $isNavActive($item);
            $hasActiveChild = $hasChildren && collect($item['children'])->contains(fn ($child) => $isNavActive($child));
            $groupHighlighted = $hasChildren && $hasActiveChild;
        @endphp

        <div>
            @if ($hasChildren)
                <button
                    type="button"
                    @click="openMenu = openMenu === @js($item['key']) ? null : @js($item['key'])"
                    :aria-expanded="openMenu === @js($item['key'])"
                    class="employee-menu-item group w-full text-left {{ $groupHighlighted ? 'employee-menu-item-active' : 'employee-menu-item-inactive' }}"
                >
                    <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $groupHighlighted ? 'bg-white/20 text-white' : 'bg-white text-slate-500 shadow-sm shadow-slate-200/60' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        @if (! empty($item['badge']) && $item['badge'] > 0)
                            <x-nav-badge :count="$item['badge']" :active="$groupHighlighted" dot-only active-ring="ring-sky-500" />
                        @endif
                    </span>
                    <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                        <span class="truncate">{{ $item['label'] }}</span>
                        <span class="flex shrink-0 items-center gap-2">
                            @if (! empty($item['badge']) && $item['badge'] > 0)
                                <x-nav-badge :count="$item['badge']" :active="$groupHighlighted" active-ring="ring-sky-500" />
                            @endif
                            <svg
                                class="h-4 w-4 shrink-0 transition-transform duration-200"
                                :class="openMenu === @js($item['key']) ? 'rotate-90' : ''"
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
                    x-show="openMenu === @js($item['key'])"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="ml-5 mt-1 space-y-0.5 border-l border-slate-200/80 pl-4"
                    style="display: none;"
                >
                    @foreach ($item['children'] as $child)
                        @php $childActive = $isNavActive($child); @endphp
                        <a
                            href="{{ $child['href'] }}"
                            @if (! empty($child['target'])) target="{{ $child['target'] }}" @endif
                            class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $childActive ? 'bg-sky-50 text-sky-700' : 'text-slate-500 hover:bg-sky-50 hover:text-sky-700' }}"
                        >
                            <span class="truncate">{{ $child['label'] }}</span>
                            @if (! empty($child['badge']) && $child['badge'] > 0)
                                <x-nav-badge :count="$child['badge']" :active="$childActive" active-ring="ring-sky-500" />
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <a
                    href="{{ $item['href'] }}"
                    @if (! empty($item['target'])) target="{{ $item['target'] }}" @endif
                    class="employee-menu-item {{ $isActive ? 'employee-menu-item-active' : 'employee-menu-item-inactive' }}"
                >
                    <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-white/20 text-white' : 'bg-white text-slate-500 shadow-sm shadow-slate-200/60' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        @if (! empty($item['badge']) && $item['badge'] > 0)
                            <x-nav-badge :count="$item['badge']" :active="$isActive" dot-only active-ring="ring-sky-500" />
                        @endif
                    </span>
                    <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if (! empty($item['badge']) && $item['badge'] > 0)
                            <x-nav-badge :count="$item['badge']" :active="$isActive" active-ring="ring-sky-500" />
                        @endif
                    </span>
                </a>
            @endif
        </div>
    @endforeach
</div>
