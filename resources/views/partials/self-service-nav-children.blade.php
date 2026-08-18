@php
    use App\Support\AccountantNavigation;
    use App\Support\EmployeeSelfServiceNavigation;

    $items = $items ?? [];
    $useMatch = $useMatch ?? false;
    $theme = $theme ?? 'manager';

    $leafActiveClass = match ($theme) {
        'accountant' => 'bg-amber-50 text-amber-800',
        default => 'bg-teal-50 text-teal-700',
    };

    $leafInactiveClass = match ($theme) {
        'accountant' => 'text-slate-600 hover:bg-amber-50/80 hover:text-amber-800',
        default => 'text-slate-500 hover:bg-teal-50 hover:text-teal-700',
    };

    $subGroupActiveClass = match ($theme) {
        'accountant' => 'bg-amber-50/80 text-amber-900',
        default => 'bg-teal-50/80 text-teal-800',
    };

    $subGroupInactiveClass = match ($theme) {
        'accountant' => 'text-slate-600 hover:bg-amber-50/60 hover:text-amber-900',
        default => 'text-slate-600 hover:bg-teal-50/60 hover:text-teal-800',
    };

    $isItemActive = function (array $item) use ($useMatch): bool {
        if ($useMatch) {
            return AccountantNavigation::isChildActive($item);
        }

        return EmployeeSelfServiceNavigation::itemIsActive($item);
    };
@endphp

@foreach ($items as $child)
    @php
        $hasNestedChildren = ! empty($child['children']) && ! empty($child['key']);
        $childActive = $isItemActive($child);
    @endphp

    @if ($hasNestedChildren)
        <div class="space-y-0.5">
            <button
                type="button"
                @click="openSubMenu = openSubMenu === @js($child['key']) ? null : @js($child['key'])"
                :aria-expanded="openSubMenu === @js($child['key'])"
                class="flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold transition {{ $childActive ? $subGroupActiveClass : $subGroupInactiveClass }}"
            >
                <span class="truncate">{{ $child['label'] }}</span>
                <svg
                    class="h-3.5 w-3.5 shrink-0 transition-transform duration-200"
                    :class="openSubMenu === @js($child['key']) ? 'rotate-90' : ''"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <div
                x-show="openSubMenu === @js($child['key'])"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="ml-2 space-y-0.5 border-l border-slate-200/80 pl-3"
                style="display: none;"
            >
                @foreach ($child['children'] as $leaf)
                    @php $leafActive = $isItemActive($leaf); @endphp
                    <a href="{{ $leaf['href'] }}"
                       class="flex items-center justify-between gap-2 rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $leafActive ? $leafActiveClass : $leafInactiveClass }}">
                        <span class="truncate">{{ $leaf['label'] }}</span>
                        @if (! empty($leaf['badge']) && $leaf['badge'] > 0)
                            <x-nav-badge :count="$leaf['badge']" :active="$leafActive" active-ring="{{ $theme === 'accountant' ? 'ring-amber-500' : 'ring-teal-500' }}" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @elseif (! empty($child['href']))
        <a href="{{ $child['href'] }}"
           @if (! empty($child['target'])) target="{{ $child['target'] }}" @endif
           class="flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $childActive ? $leafActiveClass : $leafInactiveClass }}">
            <span class="truncate">{{ $child['label'] }}</span>
            @if (! empty($child['badge']) && $child['badge'] > 0)
                <x-nav-badge :count="$child['badge']" :active="$childActive" active-ring="{{ $theme === 'accountant' ? 'ring-amber-500' : 'ring-teal-500' }}" />
            @endif
        </a>
    @endif
@endforeach
