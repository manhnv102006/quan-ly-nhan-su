@props(['label', 'value', 'note' => null, 'tone' => 'text-slate-800'])

<div class="leader-stat-card">
    <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-1.5 text-2xl font-extrabold tracking-tight {{ $tone }}">{{ $value }}</p>
    @if($note)
        <p class="mt-1 text-[11px] text-slate-400">{{ $note }}</p>
    @endif
</div>
