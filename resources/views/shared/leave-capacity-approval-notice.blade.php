@php
    $capacityContext = $capacityContext ?? null;
    $capacityEnforcement = $capacityEnforcement ?? config('leave.department_capacity_enforcement', 'override');
    $canDecide = $canDecide ?? false;
    $approveRoute = $approveRoute ?? null;
@endphp

@if ($capacityContext)
    @if ($capacityContext['warning_message'] ?? null)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
            <p class="text-sm font-semibold text-amber-950">Cảnh báo giới hạn phòng ban</p>
            <p class="mt-2 whitespace-pre-line text-xs leading-relaxed text-amber-900">{{ $capacityContext['warning_message'] }}</p>
        </div>
    @endif

    @if (($capacityContext['blocked'] ?? false) && $canDecide && $approveRoute)
        @if ($capacityEnforcement === 'override')
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-sm font-semibold text-rose-950">Phòng ban đã đạt giới hạn nghỉ</p>
                <p class="mt-2 whitespace-pre-line text-xs leading-relaxed text-rose-900">{{ $capacityContext['message'] }}</p>
                <p class="mt-3 text-xs text-rose-800">Bạn có thể duyệt thường nếu còn chỗ trống, hoặc <strong>duyệt vượt giới hạn</strong> kèm lý do (hệ thống ghi log).</p>
            </div>
        @endif
    @endif
@endif
