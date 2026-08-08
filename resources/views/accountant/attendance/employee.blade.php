@php
    $monthLabel = str_pad($filters['month'], 2, '0', STR_PAD_LEFT).'/'.$filters['year'];
@endphp

<x-accountant-layout title="Lịch sử check-in/out - {{ $employee->full_name }}" subtitle="Tháng {{ $monthLabel }}">
<div class="accountant-page space-y-6">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
            Chế độ chỉ xem — lịch sử check-in / check-out dùng đối chiếu khi tính lương kỳ {{ $monthLabel }}.
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.attendance.index') }}" class="text-emerald-700 hover:underline">Phòng ban</a>
                    @if($department)
                        <span>/</span>
                        <a href="{{ route('accountant.attendance.index', ['department_id' => $department->id, 'month' => $month]) }}" class="text-emerald-700 hover:underline">{{ $department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-700">{{ $employee->full_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">Lịch sử check-in / check-out</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                    · {{ $department?->department_name ?? '—' }}
                </p>
            </div>
            @if($department)
                <a href="{{ route('accountant.attendance.index', ['department_id' => $department->id, 'month' => $month]) }}" class="accountant-btn-secondary">← Phòng ban</a>
            @endif
        </div>

        <form method="GET" action="{{ route('accountant.attendance.index') }}" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="accountant-field">
            </div>
            <div class="min-w-[160px]">
                <label class="accountant-label">Trạng thái</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                    <option value="late" @selected($filters['status'] === 'late')>Đi muộn</option>
                    <option value="absent" @selected($filters['status'] === 'absent')>Vắng mặt</option>
                    <option value="leave" @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Xem</button>
        </form>

        @include('shared.attendance.summary-cards', ['summary' => $summary])

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-emerald-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chi tiết check-in / check-out — {{ $monthLabel }}</h3>
                <p class="text-xs text-slate-500">{{ $sessionRows->count() }} buổi · {{ $attendances->count() }} ngày</p>
            </div>

            @include('shared.attendance.session-history-table', [
                'sessionRows' => $sessionRows,
                'summary' => $summary,
                'showActions' => true,
                'detailRouteName' => 'accountant.attendance.show',
                'editRouteName' => null,
                'theme' => 'accountant',
                'emptyMessage' => 'Không có dữ liệu chấm công tháng '.$monthLabel.'.',
            ])
        </div>
    </div>
</x-accountant-layout>
