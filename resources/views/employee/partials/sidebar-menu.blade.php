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

        if (collect($menuItem['children'])->contains(fn ($child) => $isNavActive($child))) {
            $defaultOpenMenu = $menuItem['key'];
            break;
        }
    }
@endphp

<div x-data="{ openMenu: @js($defaultOpenMenu) }" class="space-y-1">
    @foreach ($navigation as $item)
        @php
            $hasChildren = ! empty($item['children']);
            $isActive = $isNavActive($item);
            $hasActiveChild = $hasChildren && collect($item['children'])->contains(fn ($child) => $isNavActive($child));
            $groupHighlighted = $hasChildren && $hasActiveChild;
        @endphp

        @if ($hasChildren)
            <button
                type="button"
                @click="openMenu = openMenu === @js($item['key']) ? null : @js($item['key'])"
                class="employee-menu-item w-full text-left {{ $groupHighlighted ? 'employee-menu-item-active' : 'employee-menu-item-inactive' }}"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $groupHighlighted ? 'bg-white/20' : 'bg-sky-50 text-sky-600' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                @if (! empty($item['badge']) && $item['badge'] > 0)
                    <span class="rounded-full {{ $groupHighlighted ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-600' }} px-1.5 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                @endif
                <svg class="h-3.5 w-3.5 shrink-0 opacity-60" :class="openMenu === @js($item['key']) ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <div x-show="openMenu === @js($item['key'])" class="ml-4 space-y-0.5 border-l-2 border-sky-100 pl-3" style="display: none;">
                @foreach ($item['children'] as $child)
                    @php $childActive = $isNavActive($child); @endphp
                    <a href="{{ $child['href'] }}"
                       @if (! empty($child['target'])) target="{{ $child['target'] }}" @endif
                       class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $childActive ? 'bg-sky-50 text-sky-800' : 'text-slate-600 hover:bg-slate-50 hover:text-sky-700' }}">
                        <span class="truncate">{{ $child['label'] }}</span>
                        @if (! empty($child['badge']) && $child['badge'] > 0)
                            <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-600">{{ $child['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <a href="{{ $item['href'] }}"
               @if (! empty($item['target'])) target="{{ $item['target'] }}" @endif
               class="employee-menu-item {{ $isActive ? 'employee-menu-item-active' : 'employee-menu-item-inactive' }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/20' : 'bg-sky-50 text-sky-600' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                @if (! empty($item['badge']) && $item['badge'] > 0)
                    <span class="rounded-full {{ $isActive ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-600' }} px-1.5 py-0.5 text-[10px] font-bold">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endif
    @endforeach
</div>
