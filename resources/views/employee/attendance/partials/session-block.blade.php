@php
    $checkInRoute = $checkInRoute ?? null;
    $checkOutRoute = $checkOutRoute ?? null;
    $checkInLabel = $checkInLabel ?? 'Check-in';
    $checkOutLabel = $checkOutLabel ?? 'Check-out';
    $wrapperClass = $class ?? '';
    $toneClasses = match ($session['status_tone'] ?? 'idle') {
        'ready' => 'text-emerald-600 bg-emerald-50',
        'active' => 'text-sky-600 bg-sky-50',
        'late' => 'text-rose-600 bg-rose-50',
        'warning' => 'text-amber-600 bg-amber-50',
        'completed' => 'text-violet-600 bg-violet-50',
        'upcoming' => 'text-slate-600 bg-slate-100',
        'missed' => 'text-rose-600 bg-rose-50',
        'waiting' => 'text-slate-500 bg-slate-100',
        default => 'text-slate-500 bg-slate-100',
    };
@endphp

<div class="{{ $wrapperClass }}">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <p class="text-xs font-bold uppercase text-slate-400">{{ $title }}</p>
        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $toneClasses }}">
            {{ $session['status_message'] ?? '' }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-slate-500 block mb-1">Giờ vào</span>
            <span class="font-medium text-slate-800">
                {{ $session['check_in'] ? $session['check_in']->format('H:i:s') : '--:--' }}
            </span>
        </div>
        <div>
            <span class="text-slate-500 block mb-1">Giờ ra</span>
            <span class="font-medium text-slate-800">
                {{ $session['check_out'] ? $session['check_out']->format('H:i:s') : '--:--' }}
            </span>
        </div>
    </div>

    @if (($session['pending_late_minutes'] ?? 0) > 0 && ! $session['check_in'])
        <p class="mt-2 text-xs font-semibold text-rose-600">
            Đang trễ {{ $session['pending_late_minutes'] }} phút (sau {{ \App\Services\EmployeeAttendanceService::GRACE_MINUTES }} phút miễn trừ)
        </p>
    @endif

    @if (! $session['check_in'] && ! $session['check_out'])
        <p class="mt-2 text-[11px] text-slate-500">
            Check-in từ {{ $session['session_start']->format('H:i') }} · Miễn trừ {{ \App\Services\EmployeeAttendanceService::GRACE_MINUTES }} phút · Check-out bắt buộc từ {{ $session['session_end']->format('H:i') }}
        </p>
    @endif

    <div class="mt-3 flex gap-2">
        @if ($session['can_check_in'] && $checkInRoute)
            <form method="POST" action="{{ $checkInRoute }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700 transition">
                    {{ $checkInLabel }}
                </button>
            </form>
        @elseif ($session['can_check_out'] && $checkOutRoute)
            <form method="POST" action="{{ $checkOutRoute }}">
                @csrf
                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                    {{ $checkOutLabel }}
                </button>
            </form>
        @elseif ($session['check_in'] && ! $session['check_out'] && ! $session['can_check_out'])
            <button type="button" disabled
                    class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-semibold cursor-not-allowed">
                Chưa đến giờ check-out ({{ $session['session_end']->format('H:i') }})
            </button>
        @elseif (! $session['check_in'] && ! $session['can_check_in'])
            <button type="button" disabled
                    class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-semibold cursor-not-allowed">
                @if (($session['status_tone'] ?? '') === 'missed')
                    Đã qua giờ check-in
                @elseif (($session['status_tone'] ?? '') === 'waiting')
                    Chưa thể check-in
                @else
                    Chưa đến giờ check-in
                @endif
            </button>
        @endif
    </div>
</div>
