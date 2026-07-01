@php
    $navigation = \App\Support\EmployeeNavigation::items();
@endphp

<x-staff-layout title="Chấm công hôm nay" role="employee" :navigation="$navigation">

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

        {{-- Ca làm --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Ca làm hôm nay</h2>

            @if ($todayShift && $todayShift->shift)
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-slate-800">{{ $todayShift->shift->shift_name }}</p>
                    <p class="text-sm text-slate-500">
                        {{ \Carbon\Carbon::parse($todayShift->shift->start_time)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($todayShift->shift->end_time)->format('H:i') }}
                    </p>
                    @if ($isFullDayShift)
                        <p class="text-xs text-amber-600 font-medium mt-1">Ca hành chính — cần chấm công 2 buổi (sáng + chiều)</p>
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
            <h2 class="text-base font-semibold text-slate-800 mb-4">Trạng thái hôm nay</h2>

            @if ($isFullDayShift)
                {{-- Buổi sáng --}}
                <div class="mb-5 pb-5 border-b border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-400 mb-2">Buổi sáng (08:00 - 12:00)</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500 block mb-1">Giờ vào</span>
                            <span class="font-medium text-slate-800">
                                {{ $attendance?->morning_check_in ? $attendance->morning_check_in->format('H:i:s') : '--:--' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-1">Giờ ra</span>
                            <span class="font-medium text-slate-800">
                                {{ $attendance?->morning_check_out ? $attendance->morning_check_out->format('H:i:s') : '--:--' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        @if (!$attendance?->morning_check_in)
                            <form method="POST" action="{{ route('attendance.check-in', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-sky-600 text-white text-xs font-semibold">Check-in sáng</button>
                            </form>
                        @elseif (!$attendance?->morning_check_out)
                            <form method="POST" action="{{ route('attendance.check-out', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold">Check-out sáng</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Buổi chiều --}}
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400 mb-2">Buổi chiều (13:00 - 17:00)</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500 block mb-1">Giờ vào</span>
                            <span class="font-medium text-slate-800">
                                {{ $attendance?->afternoon_check_in ? $attendance->afternoon_check_in->format('H:i:s') : '--:--' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-1">Giờ ra</span>
                            <span class="font-medium text-slate-800">
                                {{ $attendance?->afternoon_check_out ? $attendance->afternoon_check_out->format('H:i:s') : '--:--' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        @if ($attendance?->morning_check_out && !$attendance?->afternoon_check_in)
                            <form method="POST" action="{{ route('attendance.check-in', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-sky-600 text-white text-xs font-semibold">Check-in chiều</button>
                            </form>
                        @elseif ($attendance?->afternoon_check_in && !$attendance?->afternoon_check_out)
                            <form method="POST" action="{{ route('attendance.check-out', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold">Check-out chiều</button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-slate-500 block mb-1">Giờ vào</span>
                        <span class="font-medium text-slate-800">
                            {{ $attendance?->check_in ? $attendance->check_in->format('H:i:s') : '--:--' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-1">Giờ ra</span>
                        <span class="font-medium text-slate-800">
                            {{ $attendance?->check_out ? $attendance->check_out->format('H:i:s') : '--:--' }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if ($todayShift && $todayShift->shift)
                        @if (!$attendance?->check_in)
                            <form method="POST" action="{{ route('attendance.check-in', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-sky-600 text-white text-xs font-semibold">Check-in</button>
                            </form>
                        @elseif (!$attendance?->check_out)
                            <form method="POST" action="{{ route('attendance.check-out', $todayShift->shift->id) }}">
                                @csrf
                                <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold">Check-out</button>
                            </form>
                        @endif
                    @endif
                </div>
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

</x-staff-layout>