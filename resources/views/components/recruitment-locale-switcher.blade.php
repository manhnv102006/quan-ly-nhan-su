@props(['isDarkHeader' => false])

@php
    $current = app()->getLocale();
    $baseButton = $isDarkHeader
        ? 'border-white/15 text-white hover:border-cyan-300/60 hover:bg-cyan-300/10'
        : 'border-slate-200 bg-white text-slate-700 hover:border-cyan-300';
    $activeButton = $isDarkHeader
        ? 'border-cyan-300/80 bg-cyan-300/15 text-cyan-200'
        : 'border-cyan-500 bg-cyan-50 text-cyan-800';
@endphp

<div class="inline-flex items-center rounded-xl border p-0.5 text-xs font-black uppercase {{ $isDarkHeader ? 'border-white/15 bg-white/5' : 'border-slate-200 bg-slate-50' }}" role="group" aria-label="{{ __('recruitment.locale.switch') }}">
    <a href="{{ route('public.recruitment.locale', 'vi') }}"
       class="rounded-lg px-2.5 py-1.5 transition {{ $current === 'vi' ? $activeButton : $baseButton }}">
        VI
    </a>
    <a href="{{ route('public.recruitment.locale', 'en') }}"
       class="rounded-lg px-2.5 py-1.5 transition {{ $current === 'en' ? $activeButton : $baseButton }}">
        EN
    </a>
</div>
