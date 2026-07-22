@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = [
        'title' => 'Chấm công hôm nay',
        'subtitle' => 'Check-in và check-out ca làm việc hôm nay.',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        @if (session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h1 class="text-xl font-bold text-slate-800">Chấm công hôm nay</h1>
            <p class="text-sm text-slate-500 mt-1">{{ now()->format('d/m/Y') }}</p>
        </div>

        @if (($isBlockedDayOff ?? false))
            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 shadow-sm rounded-2xl px-5 py-4">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Hôm nay là {{ $dayOffReason }} — ngày nghỉ.</p>
                    <p class="text-xs text-amber-700 mt-1">
                        Bạn không có lịch tăng ca được duyệt nên không thể chấm công.
                        Nếu cần làm việc hôm nay, hãy gửi đơn tăng ca và chờ quản lý phê duyệt.
                    </p>
                </div>
            </div>
        @endif

        @unless (($isBlockedDayOff ?? false))
            @include('employee.attendance.partials.face-scanner')
        @endunless

        {{-- Ca làm --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Ca làm hôm nay</h2>

            @if ($todayShifts->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($todayShifts as $assignedShift)
                        @if ($assignedShift->shift)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-800">{{ $assignedShift->shift->shift_name }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ \Carbon\Carbon::parse($assignedShift->shift->start_time)->format('H:i') }}
                                    -
                                    {{ \Carbon\Carbon::parse($assignedShift->shift->end_time)->format('H:i') }}
                                </p>
                            </div>
                        @endif
                    @endforeach
                    @if ($isFullDayShift)
                        <p class="text-xs text-amber-600 font-medium">Ca hành chính — cần chấm công 2 buổi (sáng + chiều)</p>
                    @endif
                </div>
            @else
                <div class="flex items-center gap-2 text-rose-600 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Bạn chưa được gán ca làm hôm nay.
                </div>
            @endif
        </div>

        {{-- Trạng thái + nút chấm công --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-1">Trạng thái hôm nay</h2>
            <p class="text-xs text-slate-500 mb-4">
                Check-in đúng giờ ca · Miễn trừ {{ \App\Services\EmployeeAttendanceService::GRACE_MINUTES }} phút đi muộn · Bắt buộc check-out thủ công khi hết giờ ca
            </p>

            @if (($isBlockedDayOff ?? false))
                <p class="text-sm text-amber-700 font-medium">
                    Hôm nay là {{ $dayOffReason }}. Không thể chấm công ca thường khi không có đơn tăng ca được duyệt.
                </p>
            @elseif ($isFullDayShift && $attendanceSessions)
                @include('employee.attendance.partials.session-block', [
                    'title' => 'Buổi sáng (08:00 - 12:00)',
                    'session' => $attendanceSessions['morning'],
                    'checkInRoute' => $todayShift ? route('attendance.check-in', $todayShift->shift->id) : null,
                    'checkOutRoute' => $todayShift ? route('attendance.check-out', $todayShift->shift->id) : null,
                    'checkInLabel' => 'Check-in sáng',
                    'checkOutLabel' => 'Check-out sáng',
                    'class' => 'mb-5 pb-5 border-b border-slate-100',
                ])

                @include('employee.attendance.partials.session-block', [
                    'title' => 'Buổi chiều (13:00 - 17:00)',
                    'session' => $attendanceSessions['afternoon'],
                    'checkInRoute' => $todayShift ? route('attendance.check-in', $todayShift->shift->id) : null,
                    'checkOutRoute' => $todayShift ? route('attendance.check-out', $todayShift->shift->id) : null,
                    'checkInLabel' => 'Check-in chiều',
                    'checkOutLabel' => 'Check-out chiều',
                ])
            @elseif ($regularSession && $todayShift?->shift)
                @include('employee.attendance.partials.session-block', [
                    'title' => $todayShift->shift->shift_name . ' (' . \Carbon\Carbon::parse($todayShift->shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($todayShift->shift->end_time)->format('H:i') . ')',
                    'session' => $regularSession,
                    'checkInRoute' => route('attendance.check-in', $todayShift->shift->id),
                    'checkOutRoute' => route('attendance.check-out', $todayShift->shift->id),
                ])
            @elseif (! $todayShift?->shift)
                <p class="text-sm text-rose-600 font-medium">Bạn chưa được gán ca làm hôm nay.</p>
            @endif

            {{-- Tổng kết: trễ giờ --}}
            @if ($attendance)
                <div class="mt-5 pt-5 border-t border-slate-100 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Tổng thời gian đi muộn</span>
                        <span class="font-semibold {{ $attendance->late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $attendance->late_text }}
                        </span>
                    </div>
                    @if ($attendance->is_overtime)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Giờ tăng ca đã ghi nhận</span>
                            <span class="font-semibold text-amber-700">{{ $attendance->overtime_text }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Lịch tăng ca đã duyệt hôm nay --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Lịch tăng ca hôm nay</h2>
                    <p class="text-xs text-slate-500 mt-1">Chỉ check-in/check-out trong khung giờ đã được duyệt.</p>
                </div>
                <a href="{{ route('employee.overtime-requests') }}"
                   class="text-xs font-semibold text-amber-700 hover:text-amber-800">Xem đơn tăng ca →</a>
            </div>

            @if ($overtimeSessions->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center">
                    <p class="text-sm text-slate-500">Không có ca tăng ca đã duyệt cho hôm nay.</p>
                    <a href="{{ route('employee.overtime-requests.create') }}"
                       class="mt-2 inline-block text-xs font-semibold text-amber-600 hover:underline">Tạo đơn tăng ca</a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($overtimeSessions as $session)
                        @php
                            $ot = $session['request'];
                            $toneClasses = match ($session['status_tone']) {
                                'ready' => 'border-emerald-200 bg-emerald-50/60',
                                'active' => 'border-sky-200 bg-sky-50/60',
                                'completed' => 'border-violet-200 bg-violet-50/60',
                                'upcoming' => 'border-slate-200 bg-slate-50/80',
                                'missed' => 'border-rose-200 bg-rose-50/60',
                                default => 'border-slate-200 bg-white',
                            };
                            $badgeClasses = match ($session['status_tone']) {
                                'ready' => 'bg-emerald-100 text-emerald-700',
                                'active' => 'bg-sky-100 text-sky-700',
                                'completed' => 'bg-violet-100 text-violet-700',
                                'upcoming' => 'bg-slate-200 text-slate-600',
                                'missed' => 'bg-rose-100 text-rose-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <div class="rounded-2xl border p-4 sm:p-5 {{ $toneClasses }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/80 px-3 py-1.5 text-sm font-bold text-slate-800 shadow-sm">
                                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $session['start_label'] }} – {{ $session['end_label'] }}
                                        </span>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $badgeClasses }}">
                                            {{ $session['status_message'] }}
                                        </span>
                                    </div>
                                    @if ($ot->reason)
                                        <p class="mt-2 text-xs text-slate-600">Lý do: {{ $ot->reason }}</p>
                                    @endif
                                </div>
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                    {{ \App\Models\OvertimeRequest::STATUS_LABELS[$ot->status] ?? $ot->status }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-slate-500 block mb-1">Giờ vào (OT)</span>
                                    <span class="font-semibold text-slate-800">
                                        {{ $ot->actual_check_in ? $ot->actual_check_in->format('H:i:s') : '--:--' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block mb-1">Giờ ra (OT)</span>
                                    <span class="font-semibold text-slate-800">
                                        {{ $ot->actual_check_out ? $ot->actual_check_out->format('H:i:s') : '--:--' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($session['can_check_in'])
                                    <form method="POST" action="{{ route('attendance.overtime.check-in', $ot) }}">
                                        @csrf
                                        <button class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-semibold shadow-sm hover:bg-amber-700 transition">
                                            Check-in tăng ca
                                        </button>
                                    </form>
                                @elseif ($session['can_check_out'])
                                    <form method="POST" action="{{ route('attendance.overtime.check-out', $ot) }}">
                                        @csrf
                                        <button class="px-4 py-2 rounded-xl bg-orange-600 text-white text-xs font-semibold shadow-sm hover:bg-orange-700 transition">
                                            Check-out tăng ca
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled
                                            class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-semibold cursor-not-allowed">
                                        @if ($ot->status === \App\Models\OvertimeRequest::STATUS_COMPLETED)
                                            Đã chấm công xong
                                        @elseif ($session['status_tone'] === 'upcoming')
                                            Chưa đến giờ check-in
                                        @else
                                            Không thể chấm công
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Gợi ý tạo đơn tăng ca --}}
       @if ($overtimeInfo)
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="text-sm font-semibold text-amber-800">
                Bạn đã làm thêm {{ $overtimeInfo['minutes'] }} phút sau giờ kết ca
                ({{ $overtimeInfo['start_time'] }} – {{ $overtimeInfo['end_time'] }}).
            </p>
            <p class="text-xs text-amber-700 mt-1">Hãy gửi đơn tăng ca để được ghi nhận và phê duyệt.</p>
        </div>
        @php
            $otUrl = route('employee.overtime-requests.create', [
                'work_date' => $overtimeInfo['date'],
                'start_time' => $overtimeInfo['start_time'],
                'end_time' => $overtimeInfo['end_time'],
            ]);
        @endphp
        <a href="{{ $otUrl }}" class="px-4 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-semibold shadow-sm hover:bg-amber-700 transition">Tạo đơn tăng ca</a>
    </div>
@endif
    </div>

</x-dynamic-component>