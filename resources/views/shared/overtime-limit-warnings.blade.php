@php
    $warnings = $overtimeWarnings ?? ($overtimeLimits['warnings'] ?? []);
@endphp

@if (! empty($warnings))
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm space-y-3">
        <p class="text-sm font-semibold text-amber-950">Cảnh báo giới hạn tăng ca</p>
        @foreach ($warnings as $warning)
            <p class="text-xs leading-relaxed text-amber-900">{{ $warning }}</p>
        @endforeach
    </div>
@endif
