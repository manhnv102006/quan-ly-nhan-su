@php
    $user = Auth::user();
    $isManager = $user->role->name === 'manager';

    $navigation = [
        ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
        ['label' => 'Chấm công', 'href' => route('attendance.index'), 'route' => 'attendance.index', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0?', 'note' => 'Check-in / Check-out'],
        ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
        ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Tổng thu nhập'],
        ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
    ];
@endphp

<x-staff-layout
    title="Chấm công hôm nay"
    role="employee"
    :navigation="$navigation"
>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h1 class="text-xl font-bold text-slate-800">
                Chấm công hôm nay
            </h1>

            <p class="text-sm text-slate-500 mt-1">
                {{ now()->format('d/m/Y') }}
            </p>
        </div>

        {{-- Ca làm --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">

            <h2 class="text-base font-semibold text-slate-800 mb-4">
                Ca làm hôm nay
            </h2>

            @if ($todayShift && $todayShift->shift)

                <div class="space-y-1">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $todayShift->shift->shift_name }}
                    </p>

                    <p class="text-sm text-slate-500">
                        {{ \Carbon\Carbon::parse($todayShift->shift->start_time)->format('H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($todayShift->shift->end_time)->format('H:i') }}
                    </p>
                </div>

            @else

                <div class="flex items-center gap-2 text-rose-600 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Bạn chưa được gán ca làm hôm nay.
                </div>

            @endif

        </div>

        {{-- Trạng thái --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">

            <h2 class="text-base font-semibold text-slate-800 mb-4">
                Trạng thái hôm nay
            </h2>

            @if ($attendance)

                <div class="space-y-3 text-sm">

                    <div class="flex justify-between">
                        <span class="text-slate-500">Trạng thái</span>
                        <span class="font-semibold text-emerald-600">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Giờ vào</span>
                        <span class="font-medium text-slate-800">
                            {{ $attendance->check_in
                                ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s')
                                : '--:--' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Giờ ra</span>
                        <span class="font-medium text-slate-800">
                            {{ $attendance->check_out
                                ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s')
                                : '--:--' }}
                        </span>
                    </div>

                </div>

            @else

                <div class="flex items-center gap-2 text-rose-600 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Chưa chấm công hôm nay
                </div>

            @endif

        </div>

    </div>

</x-staff-layout>