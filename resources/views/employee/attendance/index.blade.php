@php
    $user = Auth::user();
    $isManager = $user->role->name === 'manager';

    $navigation = [
        ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
        ['label' => 'Chấm công', 'href' => route('attendance.index'), 'route' => 'attendance.index', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Check-in / Check-out'],
        ['label' => 'Tăng ca', 'href' => route('employee.overtime-requests'), 'route' => 'employee.overtime-requests*', 'icon' => 'M12 6v6l4 2', 'note' => 'Đơn xin tăng ca'],
        ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
        ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Tổng thu nhập'],
        ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
    ];
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
                <div class="mt-5 pt-5 border-t border-slate-100 flex justify-between text-sm">
                    <span class="text-slate-500">Tổng thời gian đi muộn</span>
                    <span class="font-semibold {{ $attendance->late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $attendance->late_text }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Gợi ý tạo đơn tăng ca --}}
        @if ($overtimeInfo)
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-sm font-semibold text-amber-800">
                        Bạn đã làm thêm {{ $overtimeInfo['minutes'] }} phút sau giờ kết ca ({{ $overtimeInfo['start_time'] }} - {{ $overtimeInfo['end_time'] }}).
                    </p>
                    <p class="text-xs text-amber-700 mt-1">Hãy gửi đơn tăng ca để được ghi nhận và phê duyệt.</p>
                </div>
                
                    href="{{ route('employee.overtime-requests.create', [
                        'date' => $overtimeInfo['date'],
                        'start_time' => $overtimeInfo['start_time'],
                        'end_time' => $overtimeInfo['end_time'],
                    ]) }}"
                    class="px-4 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-semibold shadow-sm hover:bg-amber-700 transition"
                >
                    Tạo đơn tăng ca
                </a>
            </div>
        @endif

    </div>

</x-staff-layout>