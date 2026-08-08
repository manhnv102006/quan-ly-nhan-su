<x-admin-layout title="Lịch sử check-in/out — {{ $employee->full_name }}">
    <div class="space-y-6">

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
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Lịch sử check-in / check-out</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-800">{{ $employee->full_name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $employee->employee_code }}
                        @if($employee->position) · {{ $employee->position->position_name }} @endif
                        · {{ $department->department_name }}
                    </p>
                </div>
                <a href="{{ route('admin.employees.show', $employee) }}"
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    ← Hồ sơ nhân viên
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('admin.attendances.employee', [$department, $employee]) }}" method="GET"
                  class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tháng</label>
                    <select name="month" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($filters['month'] == $m)>Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Năm</label>
                    <select name="year" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach ([now()->year - 1, now()->year, now()->year + 1] as $y)
                            <option value="{{ $y }}" @selected($filters['year'] == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        <option value="">Tất cả</option>
                        <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                        <option value="late" @selected($filters['status'] === 'late')>Đi muộn</option>
                        <option value="absent" @selected($filters['status'] === 'absent')>Vắng mặt</option>
                        <option value="leave" @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-500/20 hover:bg-violet-700">Xem</button>
                <a href="{{ route('admin.attendances.employee', [$department, $employee]) }}"
                   class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Tháng này</a>
            </form>
        </div>

        @include('shared.attendance.summary-cards', ['summary' => $summary])

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">
                    Chi tiết check-in / check-out
                    — tháng {{ str_pad($filters['month'], 2, '0', STR_PAD_LEFT) }}/{{ $filters['year'] }}
                </h3>
                <p class="text-xs text-slate-500">{{ $sessionRows->count() }} buổi · {{ $attendances->count() }} ngày chấm công</p>
            </div>

            @include('shared.attendance.session-history-table', [
                'sessionRows' => $sessionRows,
                'summary' => $summary,
                'showActions' => true,
                'detailRouteName' => 'admin.attendances.show',
                'editRouteName' => 'admin.attendances.edit',
                'emptyMessage' => 'Không có dữ liệu chấm công tháng '.str_pad($filters['month'], 2, '0', STR_PAD_LEFT).'/'.$filters['year'].'.',
            ])
        </div>
    </div>
</x-admin-layout>
