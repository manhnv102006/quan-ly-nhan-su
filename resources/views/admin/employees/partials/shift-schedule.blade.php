<div id="lich-ca-lam" class="bg-white rounded-3xl shadow-sm border border-slate-100">
    <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-800">Lịch ca làm</h3>
            <p class="text-xs text-slate-500 mt-0.5">Ca được gán cho nhân viên theo từng ngày trong tháng</p>
        </div>
        <a href="{{ route('admin.employee-shifts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
            + Gán ca
        </a>
    </div>

    <div class="p-6 space-y-6">
        <form method="GET" action="{{ route('admin.employees.show', $employee) }}"
              class="flex flex-wrap items-end gap-3">
            <div class="space-y-2">
                <label for="shift_month" class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tháng xem lịch</label>
                <input id="shift_month" type="month" name="shift_month"
                       value="{{ $shiftMonth }}"
                       class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 transition">
                Xem
            </button>
            <a href="{{ route('admin.employee-shifts.index', ['employee_id' => $employee->id, 'work_month' => $shiftMonth]) }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                Mở danh sách gán ca
            </a>
        </form>

        @include('admin.employee-shifts.partials.current-shift-summary', [
            'summaries' => $shiftMonthSummary['summaries'],
            'monthLabel' => $shiftMonthSummary['month_label'],
            'showEmployee' => false,
        ])

        @if ($shiftMonthSummary['summaries'] === [])
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center">
                <p class="text-sm font-medium text-slate-600">
                    Chưa có ca nào được gán trong tháng {{ $shiftMonthSummary['month_label'] }}.
                </p>
                <a href="{{ route('admin.employee-shifts.create') }}"
                   class="mt-3 inline-flex text-sm font-semibold text-violet-600 hover:text-violet-800">
                    Gán ca cho nhân viên này →
                </a>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80">
                            <th class="px-4 py-3 font-semibold text-slate-600">Ngày</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Thứ</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Ca làm</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Giờ ca</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employeeShiftsInMonth as $assignment)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/60">
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $assignment->work_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ $assignment->work_date->locale('vi')->isoFormat('dddd') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-violet-700">
                                    {{ $assignment->shift?->shift_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if ($assignment->shift)
                                        {{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
