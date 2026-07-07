<x-admin-layout title="Chấm công — {{ $employee->full_name }}">
    <div class="space-y-6">

        {{-- Breadcrumb --}}
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-3">
                <a href="{{ route('admin.attendances') }}" class="hover:text-violet-600 transition">Chấm công</a>
                <span>/</span>
                <a href="{{ route('admin.attendances.department', $department) }}"
                   class="hover:text-violet-600 transition">{{ $department->department_name }}</a>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ $employee->full_name }}</span>
            </nav>

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Chi tiết chấm công</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-800">{{ $employee->full_name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $employee->employee_code }}
                        @if($employee->position)
                            · {{ $employee->position->position_name }}
                        @endif
                        · {{ $department->department_name }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Bộ lọc tháng / năm / trạng thái --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('admin.attendances.employee', [$department, $employee]) }}" method="GET"
                  class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tháng</label>
                    <select name="month"
                            class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                                   transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($filters['month'] == $m)>
                                Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Năm</label>
                    <select name="year"
                            class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                                   transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach ([now()->year - 1, now()->year, now()->year + 1] as $y)
                            <option value="{{ $y }}" @selected($filters['year'] == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                    <select name="status"
                            class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                                   transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        <option value="">Tất cả</option>
                        <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                        <option value="late"    @selected($filters['status'] === 'late')>Đi muộn</option>
                        <option value="absent"  @selected($filters['status'] === 'absent')>Vắng mặt</option>
                        <option value="leave"   @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold
                               text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Xem
                </button>
                <a href="{{ route('admin.attendances.employee', [$department, $employee]) }}"
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5
                          text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Tháng này
                </a>
            </form>
        </div>

        {{-- Thống kê tóm tắt --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
            @foreach ([
                ['label' => 'Tổng ngày',   'value' => $summary['total'],       'tone' => 'text-slate-800',   'bg' => 'bg-slate-50'],
                ['label' => 'Đi làm',      'value' => $summary['present'],     'tone' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                ['label' => 'Đi muộn',     'value' => $summary['late'],        'tone' => 'text-amber-600',   'bg' => 'bg-amber-50'],
                ['label' => 'Vắng',        'value' => $summary['absent'],      'tone' => 'text-rose-600',    'bg' => 'bg-rose-50'],
                ['label' => 'Nghỉ phép',   'value' => $summary['leave'],       'tone' => 'text-sky-600',     'bg' => 'bg-sky-50'],
                ['label' => 'Tổng giờ',    'value' => $summary['total_hours'], 'tone' => 'text-violet-700',  'bg' => 'bg-violet-50'],
            ] as $card)
                <div class="rounded-2xl border border-slate-100 {{ $card['bg'] }} p-5 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-1.5 text-2xl font-extrabold tracking-tight {{ $card['tone'] }}">
                        {{ $card['value'] }}{{ $card['label'] === 'Tổng giờ' ? 'h' : '' }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Bảng chấm công --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">
                    Chi tiết chấm công
                    — tháng {{ str_pad($filters['month'], 2, '0', STR_PAD_LEFT) }}/{{ $filters['year'] }}
                </h3>
                <p class="text-xs text-slate-500">{{ $attendances->count() }} bản ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ngày</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ca làm</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Check Out</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Đi muộn</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-500">Giờ làm</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $index => $att)
                            @php
                                $rowBg = match($att->status) {
                                    'late'   => 'bg-amber-50/50',
                                    'absent' => 'bg-rose-50/50',
                                    'leave'  => 'bg-sky-50/50',
                                    default  => '',
                                };
                            @endphp
                            <tr class="border-t transition hover:bg-slate-50 {{ $rowBg }}">
                                <td class="px-6 py-3.5 text-sm text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="font-semibold text-slate-800">
                                        {{ \Carbon\Carbon::parse($att->attendance_date)->format('d/m/Y') }}
                                    </span>
                                    <span class="ml-1 text-xs text-slate-400">
                                        ({{ \Carbon\Carbon::parse($att->attendance_date)->locale('vi')->isoFormat('ddd') }})
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-sm text-slate-600">
                                    {{ $att->employeeShift?->shift?->shift_name ?? $att->shift?->shift_name ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-sm font-mono text-slate-700">
                                    {{ $att->check_in?->format('H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-sm font-mono text-slate-700">
                                    {{ $att->check_out?->format('H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-center text-sm">
                                    @if(($att->late_minutes ?? 0) > 0)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                            +{{ $att->late_minutes }} phút
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    @switch($att->status)
                                        @case('present')
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đi làm</span>
                                            @break
                                        @case('late')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Đi muộn</span>
                                            @break
                                        @case('leave')
                                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Nghỉ phép</span>
                                            @break
                                        @case('absent')
                                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Vắng mặt</span>
                                            @break
                                        @default
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $att->status }}</span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-3.5 text-right font-medium text-slate-700">
                                    {{ $att->work_hours > 0 ? $att->work_hours . 'h' : '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('admin.attendances.show', $att) }}"
                                           class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs
                                                  font-semibold text-slate-700 transition hover:bg-slate-200">
                                            Chi tiết
                                        </a>
                                        <a href="{{ route('admin.attendances.edit', $att) }}"
                                           class="inline-flex items-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs
                                                  font-semibold text-white transition hover:bg-violet-700">
                                            Sửa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-600">
                                        Không có dữ liệu chấm công tháng {{ str_pad($filters['month'], 2, '0', STR_PAD_LEFT) }}/{{ $filters['year'] }}.
                                    </p>
                                    <a href="{{ route('admin.attendances.employee', [$department, $employee]) }}"
                                       class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                                        Xem tháng hiện tại
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($attendances->isNotEmpty())
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="7" class="px-6 py-3 text-right text-xs font-bold uppercase text-slate-500">
                                    Tổng cộng
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-violet-700">
                                    {{ $summary['total_hours'] }}h
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
