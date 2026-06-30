@props(['href' => null])

@php
    $classes = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes . ' hover:bg-slate-200 transition']) }}>
        Chỉ xem
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        Chỉ xem
    </span>
@endif
