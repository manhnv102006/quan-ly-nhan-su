@props([
    'field' => 'leave_capacity',
    'title' => 'Giới hạn nghỉ phép phòng ban',
])

@error($field)
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950 shadow-sm']) }}>
        <p class="font-semibold text-amber-950">{{ $title }}</p>
        <div class="mt-2 space-y-1 text-xs leading-relaxed text-amber-900 whitespace-pre-line">{{ $message }}</div>
    </div>
@enderror
