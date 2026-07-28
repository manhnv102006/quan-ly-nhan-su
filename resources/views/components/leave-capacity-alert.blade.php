@props([
    'field' => 'leave_capacity',
    'title' => 'Giới hạn nghỉ phép phòng ban',
])

@error($field)
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm']) }}>
        <p class="text-sm font-semibold text-amber-950">{{ $title }}</p>
        <p class="mt-2 whitespace-pre-line text-xs leading-relaxed text-amber-900">{{ $message }}</p>
    </div>
@enderror
