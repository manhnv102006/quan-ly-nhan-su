@props([
    'count' => 0,
    'active' => false,
    'dotOnly' => false,
    'activeRing' => 'ring-violet-500',
])

@if ($count > 0)
    @if ($dotOnly)
        <span {{ $attributes->merge(['class' => 'absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 '.($active ? $activeRing : 'ring-white')]) }}></span>
    @else
        <span {{ $attributes->merge(['class' => 'inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white '.($active ? 'ring-1 ring-white/40' : '')]) }}>
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
@endif
