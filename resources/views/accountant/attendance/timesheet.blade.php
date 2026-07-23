@php
    $monthLabel = \Carbon\Carbon::parse($month.'-01')->format('m/Y');
    $stat = fn ($employee, string $key) => (int) ($employee->attendance_stats?->$key ?? 0);
    $hours = fn ($employee) => round((float) ($employee->attendance_stats?->total_hours ?? 0), 1);
    $ot = fn ($employee) => round((float) ($employee->attendance_stats?->overtime_hours ?? 0), 1);
@endphp

<x-accountant-layout title="Bảng công tháng" subtitle="Tháng {{ $monthLabel }} — phục vụ tính lương">
<div class="accountant-page">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            Bảng công tổng hợp toàn công ty — chỉ xem, dùng đối chiếu trước khi tính/duyệt lương.
        </div>

        <div>
            <h2 class="text-2xl font-bold text-slate-900">Bảng công tháng {{ $monthLabel }}</h2>
            <p class="text-sm text-slate-500">Ngày công tính lương = đi làm + đi muộn</p>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[180px]">
                <label class="accountant-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="accountant-field">
            </div>
            <div class="min-w-[180px]">
                <label class="accountant-label">Phòng ban</label>
                <select name="department_id" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                            {{ $department->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên, mã NV..." class="accountant-field">
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if(request()->hasAny(['search', 'department_id']))
                <a href="{{ route('accountant.attendance.timesheet', ['month' => $month]) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Ngày công tính lương', 'value' => $totals['payable_days'], 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng giờ làm', 'value' => $totals['total_hours'].'h', 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng giờ OT', 'value' => $totals['overtime_hours'].'h', 'tone' => 'text-violet-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-emerald-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Danh sách nhân viên</h3>
                <p class="text-xs text-slate-500">{{ $employees->total() }} nhân viên</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="bg-emerald-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3">Chức vụ</th>
                            <th class="px-4 py-3 text-center">Ngày công TL</th>
                            <th class="px-4 py-3 text-center">Đi làm</th>
                            <th class="px-4 py-3 text-center">Muộn</th>
                            <th class="px-4 py-3 text-center">Vắng</th>
                            <th class="px-4 py-3 text-center">Nghỉ phép</th>
                            <th class="px-4 py-3 text-right">Giờ làm</th>
                            <th class="px-4 py-3 text-right">OT</th>
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
                                <td class="px-4 py-3">{{ $employee->department?->department_name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full bg-emerald-100 px-2.5 py-1 text-sm font-bold text-emerald-800">
                                        {{ $employee->payable_days }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-emerald-700">{{ $stat($employee, 'present') }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $stat($employee, 'late') }}</td>
                                <td class="px-4 py-3 text-center text-rose-700">{{ $stat($employee, 'absent') }}</td>
                                <td class="px-4 py-3 text-center text-sky-700">{{ $stat($employee, 'leave') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $hours($employee) }}h</td>
                                <td class="px-4 py-3 text-right text-violet-700">{{ $ot($employee) }}h</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('accountant.attendance.index', ['employee_id' => $employee->id, 'month' => $month]) }}"
                                       class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-14 text-center text-slate-500">Không có nhân viên phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</x-accountant-layout>
