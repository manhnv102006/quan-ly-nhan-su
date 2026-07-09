@props([
    'label',
    'value',
    'hint' => null,
    'tag' => null,
    'icon' => null,
    'tone' => 'from-teal-500 to-emerald-600',
    'border' => 'border-teal-100/80',
])

<div {{ $attributes->merge(['class' => "manager-stat-card border {$border} bg-white/90"]) }}>
    <div class="flex items-start justify-between">
        @if ($icon)
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $tone }} text-white shadow-lg shadow-teal-200">
                {!! $icon !!}
            </div>
        @endif
        @if ($tag)
            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-teal-700">{{ $tag }}</span>
        @endif
    </div>
    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ $value }}</p>
    <p class="mt-1 text-sm font-medium text-slate-500">{{ $label }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
