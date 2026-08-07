<x-admin-layout title="Danh sách gán ca">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách gán ca</h2>
                <p class="text-sm text-slate-500 mt-1">Tra cứu ca đã gán theo nhân viên, tháng và tên ca — xem tóm tắt lịch làm trong tháng.</p>
            </div>
            <a href="{{ route('admin.employee-shifts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                + Gán ca mới
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Bộ lọc --}}
        <div class="rounded-3xl border border-slate-100 bg-white p-5 sm:p-6 shadow-sm">
            <form method="GET" action="{{ route('admin.employee-shifts.index') }}"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="space-y-2">
                    <label for="employee_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Nhân viên</label>
                    <select id="employee_id" name="employee_id"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                        <option value="">Tất cả nhân viên</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($filters['employee_id'] == $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="work_month" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tháng</label>
                    <input id="work_month" type="month" name="work_month"
                           value="{{ $filters['work_month'] }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                </div>
                <div class="space-y-2">
                    <label for="shift_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Ca làm</label>
                    <select id="shift_id" name="shift_id"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                        <option value="">Tất cả ca</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" @selected($filters['shift_id'] == $shift->id)>
                                {{ $shift->shift_name }}
                                ({{ $shift->start_time?->format('H:i') ?? substr((string) $shift->start_time, 0, 5) }}
                                - {{ $shift->end_time?->format('H:i') ?? substr((string) $shift->end_time, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-700 transition">
                        Lọc
                    </button>
                    <a href="{{ route('admin.employee-shifts.index') }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Xóa lọc
                    </a>
                </div>
            </form>
        </div>

        {{-- Ca hiện tại --}}
        @include('admin.employee-shifts.partials.current-shift-summary', [
            'summaries' => $monthSummary['summaries'],
            'monthLabel' => $monthSummary['month_label'],
            'showEmployee' => ! $filters['employee_id'],
        ])

        @if ($filters['employee_id'] && $monthSummary['summaries'] === [])
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                <p class="text-sm font-medium text-slate-600">Nhân viên này chưa được gán ca trong tháng {{ $monthSummary['month_label'] }}.</p>
                <a href="{{ route('admin.employee-shifts.create') }}"
                   class="mt-3 inline-flex text-sm font-semibold text-violet-600 hover:text-violet-800">
                    + Gán ca ngay
                </a>
            </div>
        @endif

        {{-- Bảng chi tiết --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết từng ngày</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $employeeShifts->total() }} lịch ca phù hợp bộ lọc</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="p-4 font-semibold text-slate-600">Nhân viên</th>
                            <th class="p-4 font-semibold text-slate-600">Phòng ban</th>
                            <th class="p-4 font-semibold text-slate-600">Ca</th>
                            <th class="p-4 font-semibold text-slate-600">Giờ ca</th>
                            <th class="p-4 font-semibold text-slate-600">Ngày</th>
                            <th class="p-4 font-semibold text-slate-600">Thứ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeShifts as $item)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                <td class="p-4">
                                    <a href="{{ route('admin.employees.show', $item->employee_id) }}#lich-ca-lam"
                                       class="font-medium text-violet-700 hover:text-violet-900">
                                        {{ $item->employee?->full_name ?? '—' }}
                                    </a>
                                    <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $item->employee?->department?->department_name ?? '—' }}
                                </td>
                                <td class="p-4 font-medium text-slate-800">
                                    {{ $item->shift?->shift_name ?? '—' }}
                                </td>
                                <td class="p-4 text-slate-600">
                                    @if ($item->shift)
                                        {{ \Carbon\Carbon::parse($item->shift->start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($item->shift->end_time)->format('H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-4 text-slate-700">
                                    {{ $item->work_date->format('d/m/Y') }}
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $item->work_date->locale('vi')->isoFormat('dddd') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-slate-500">
                                    Không có lịch gán ca phù hợp bộ lọc.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employeeShifts->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $employeeShifts->links() }}
                </div>
            @endif
        </div>
    </div>

</x-admin-layout>
