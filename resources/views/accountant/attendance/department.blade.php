@php
    $monthLabel = \Carbon\Carbon::parse($month.'-01')->format('m/Y');
    $stat = fn ($employee, string $key) => (int) ($employee->attendance_stats?->$key ?? 0);
    $hours = fn ($employee) => round((float) ($employee->attendance_stats?->total_hours ?? 0), 1);
@endphp

<x-accountant-layout title="Chấm công - {{ $department->department_name }}" subtitle="Bảng công tháng {{ $monthLabel }}">
    @include('accountant.attendance.partials.sub-nav', ['active' => 'departments'])

    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.attendance.index') }}" class="text-emerald-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $employees->count() }} nhân viên · Tháng {{ $monthLabel }}</p>
            </div>
            <a href="{{ route('accountant.attendance.index') }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="accountant-field" onchange="this.form.submit()">
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tên, mã NV..." class="accountant-field">
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Ngày công tính lương', 'value' => $totals['payable_days'], 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng giờ làm', 'value' => $totals['total_hours'].'h', 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Vắng mặt', 'value' => $totals['absent'], 'tone' => 'text-rose-600'])
            @include('accountant.partials.stat-card', ['label' => 'Nghỉ phép', 'value' => $totals['leave'], 'tone' => 'text-sky-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-emerald-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Tổng hợp chấm công</h3>
                <p class="text-xs text-slate-500">Ngày công tính lương = đi làm + đi muộn (theo quy tắc tính lương)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="bg-emerald-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Chức vụ</th>
                            <th class="px-4 py-3 text-center">Ngày công TL</th>
                            <th class="px-4 py-3 text-center">Đi làm</th>
                            <th class="px-4 py-3 text-center">Muộn</th>
                            <th class="px-4 py-3 text-center">Vắng</th>
                            <th class="px-4 py-3 text-center">Nghỉ phép</th>
                            <th class="px-4 py-3 text-right">Giờ làm</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800">
                                        {{ $employee->payable_days }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-emerald-700">{{ $stat($employee, 'present') }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $stat($employee, 'late') }}</td>
                                <td class="px-4 py-3 text-center text-rose-700">{{ $stat($employee, 'absent') }}</td>
                                <td class="px-4 py-3 text-center text-sky-700">{{ $stat($employee, 'leave') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $hours($employee) }}h</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('accountant.attendance.index', ['employee_id' => $employee->id, 'month' => $month]) }}"
                                       class="accountant-btn-secondary !py-1.5 !text-xs">Bảng công</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center text-slate-500">Phòng ban chưa có nhân viên.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
