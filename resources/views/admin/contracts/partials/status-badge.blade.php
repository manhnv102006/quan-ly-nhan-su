@props(['contract'])

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-2.5 py-1 text-xs font-bold ' . $contract->status_tailwind_class]) }}>
    {{ $contract->status_label }}
</span>
