@php
    $filterRoute = $filterRoute ?? route('admin.attendances');
    $clearFilterRoute = $clearFilterRoute ?? route('admin.attendances');
    $showDepartmentColumn = $showDepartmentColumn ?? true;
    $scopeLabel = $scopeLabel ?? 'Phòng ban';
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<section class="space-y-6">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Tổng hợp</p>
        <h2 class="mt-1 text-lg font-bold text-slate-800">{{ $scopeLabel }}</h2>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Tổng bản ghi', 'value' => $stats['total'], 'tone' => 'text-slate-800'],
            ['label' => 'Đi làm', 'value' => $stats['present'], 'tone' => 'text-emerald-600'],
            ['label' => 'Đi muộn', 'value' => $stats['late'], 'tone' => 'text-amber-600'],
            ['label' => 'Nghỉ phép', 'value' => $stats['leave'], 'tone' => 'text-rose-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Tìm kiếm &amp; lọc</h3>
                <p class="text-xs text-slate-500">Tìm theo tên, mã nhân viên, ngày hoặc trạng thái</p>
            </div>
            @if($hasFilters)
                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                    Đang lọc · {{ $attendances->total() }} kết quả
                </span>
            @endif
        </div>

        <form action="{{ $filterRoute }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label for="search" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Từ khóa</label>
                <input type="text" id="search" name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Tên hoặc mã nhân viên"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
            </div>

            <div>
                <label for="date" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ngày</label>
                <input type="date" id="date" name="date"
                       value="{{ $filters['date'] ?? '' }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả trạng thái</option>
                    <option value="present" @selected(($filters['status'] ?? '') === 'present')>Đi làm</option>
                    <option value="late" @selected(($filters['status'] ?? '') === 'late')>Đi muộn</option>
                    <option value="leave" @selected(($filters['status'] ?? '') === 'leave')>Nghỉ phép</option>
                    <option value="absent" @selected(($filters['status'] ?? '') === 'absent')>Vắng mặt</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-4">
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Tìm kiếm
                </button>
                @if($hasFilters)
                    <a href="{{ $clearFilterRoute }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <h3 class="text-sm font-bold text-slate-800">Danh sách chấm công</h3>
            <p class="text-xs text-slate-500">{{ $attendances->count() }} / {{ $attendances->total() }} bản ghi — {{ $scopeLabel }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                        @if ($showDepartmentColumn)
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ngày</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ca làm</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Giờ làm</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $index => $attendance)
                        <tr class="border-t transition hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $attendance->employee?->full_name }}
                            </td>
                            @if ($showDepartmentColumn)
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $attendance->employee?->department?->department_name ?? '—' }}
                                </td>
                            @endif
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $attendance->attendance_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $attendance->employeeShift?->shift?->shift_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $attendance->check_in?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $attendance->check_out?->format('H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @switch($attendance->status)
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
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Không xác định</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $attendance->work_hours ?? 0 }} giờ
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.attendances.show', $attendance) }}"
                                   class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-700">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showDepartmentColumn ? 10 : 9 }}" class="py-14 text-center">
                                <p class="text-sm font-semibold text-slate-600">Chưa có dữ liệu chấm công.</p>
                                @if($hasFilters)
                                    <a href="{{ $clearFilterRoute }}" class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                                        Xóa bộ lọc để xem tất cả
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($attendances->hasPages())
            <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</section>
