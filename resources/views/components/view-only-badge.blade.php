@props(['href' => null])

@php
    if ($href) {
        $classes = 'inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700 transition hover:bg-sky-100';
        $label = 'Chi tiết';
    } else {
        $classes = 'inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-500';
        $label = 'Chỉ xem';
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $label }}
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $label }}
    </span>
@endif
