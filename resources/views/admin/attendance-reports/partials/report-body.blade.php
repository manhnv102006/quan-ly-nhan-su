{{-- Thống kê --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
        <p class="text-sm text-slate-500">Đi làm</p>
        <h2 class="mt-1 text-3xl font-extrabold text-emerald-600">{{ number_format($stats['present']) }}</h2>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5">
        <p class="text-sm text-slate-500">Đi muộn</p>
        <h2 class="mt-1 text-3xl font-extrabold text-amber-600">{{ number_format($stats['late']) }}</h2>
    </div>
    <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-5">
        <p class="text-sm text-slate-500">Nghỉ phép</p>
        <h2 class="mt-1 text-3xl font-extrabold text-sky-600">{{ number_format($stats['leave']) }}</h2>
    </div>
    <div class="rounded-2xl border border-rose-100 bg-rose-50/60 p-5">
        <p class="text-sm text-slate-500">Vắng mặt</p>
        <h2 class="mt-1 text-3xl font-extrabold text-rose-600">{{ number_format($stats['absent']) }}</h2>
    </div>
    <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-5">
        <p class="text-sm text-slate-500">Nhân viên có chấm công</p>
        <h2 class="mt-1 text-3xl font-extrabold text-violet-600">{{ number_format($stats['employee_count']) }}</h2>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Tổng giờ làm</p>
        <h2 class="mt-1 text-2xl font-bold text-violet-600">{{ number_format($stats['total_hours'], 2) }} giờ</h2>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Tổng phút đi muộn</p>
        <h2 class="mt-1 text-2xl font-bold text-orange-600">{{ number_format($stats['late_minutes']) }} phút</h2>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Đi muộn nhiều nhất</p>
        <h2 class="mt-1 text-lg font-bold text-rose-600">{{ $stats['top_late_employee'] ?? 'Không có' }}</h2>
    </div>
</div>

{{-- Bảng chi tiết --}}
<div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="font-semibold text-slate-800">Chi tiết chấm công</h3>
        <p class="text-xs text-slate-500 mt-1">{{ $stats['record_count'] }} bản ghi — {{ $scopeLabel }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                    <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                    @if ($showDepartmentColumn)
                        <th class="px-4 py-3 text-xs font-bold uppercase text-slate-400">Phòng ban</th>
                    @endif
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Ngày</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Ca làm</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Giờ vào</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Giờ ra</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Đi muộn</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Giờ làm</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attendances as $attendance)
                    @php
                        $checkIn = $attendance->check_in ?? $attendance->morning_check_in;
                        $checkOut = $attendance->check_out ?? $attendance->afternoon_check_out;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $attendance->employee?->full_name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $attendance->employee?->employee_code ?? '' }}</p>
                        </td>
                        @if ($showDepartmentColumn)
                            <td class="px-4 py-3 text-slate-600">
                                {{ $attendance->employee?->department?->department_name ?? '—' }}
                            </td>
                        @endif
                        <td class="px-4 py-3 text-center text-slate-700">
                            {{ $attendance->attendance_date?->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700">
                            {{ $attendance->shift?->shift_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700">
                            {{ $checkIn ? $checkIn->format('H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700">
                            {{ $checkOut ? $checkOut->format('H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-orange-600">
                            {{ $attendance->display_late_minutes ?? 0 }} phút
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700">
                            {{ number_format((float) ($attendance->work_hours ?? 0), 2) }}h
                        </td>
                        <td class="px-4 py-3 text-center">
                            @switch($attendance->status)
                                @case('present')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Đi làm</span>
                                    @break
                                @case('late')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Đi muộn</span>
                                    @break
                                @case('leave')
                                    <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700">Nghỉ phép</span>
                                    @break
                                @default
                                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">Vắng mặt</span>
                            @endswitch
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showDepartmentColumn ? 9 : 8 }}" class="px-4 py-12 text-center text-slate-500">
                            Không có dữ liệu chấm công trong kỳ đã chọn.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
