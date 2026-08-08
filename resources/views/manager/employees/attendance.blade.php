<x-manager-layout title="Lịch sử check-in/out" subtitle="{{ $employee->full_name }}">
    <div class="manager-page space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('manager.employees.show', $employee) }}"
                   class="mb-3 inline-flex text-sm font-semibold text-teal-700 hover:underline">← Hồ sơ nhân viên</a>
                <h2 class="text-2xl font-bold text-slate-900">Lịch sử check-in / check-out</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                    · {{ $department?->department_name ?? '—' }}
                </p>
            </div>
        </div>

        <div class="manager-card p-5">
            <form action="{{ route('manager.employees.attendance', $employee) }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Tháng</label>
                    <select name="month" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($filters['month'] == $m)>Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Năm</label>
                    <select name="year" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        @foreach ([now()->year - 1, now()->year, now()->year + 1] as $y)
                            <option value="{{ $y }}" @selected($filters['year'] == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Trạng thái</label>
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                        <option value="">Tất cả</option>
                        <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                        <option value="late" @selected($filters['status'] === 'late')>Đi muộn</option>
                        <option value="absent" @selected($filters['status'] === 'absent')>Vắng mặt</option>
                        <option value="leave" @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                    </select>
                </div>
                <button type="submit" class="manager-btn-primary">Xem</button>
            </form>
        </div>

        @include('shared.attendance.summary-cards', ['summary' => $summary])

        <div class="manager-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">
                    Chi tiết tháng {{ str_pad($filters['month'], 2, '0', STR_PAD_LEFT) }}/{{ $filters['year'] }}
                </h3>
                <p class="text-xs text-slate-500">{{ $sessionRows->count() }} buổi · {{ $attendances->count() }} ngày</p>
            </div>

            @include('shared.attendance.session-history-table', [
                'sessionRows' => $sessionRows,
                'summary' => $summary,
                'showActions' => false,
                'emptyMessage' => 'Không có dữ liệu chấm công trong tháng đã chọn.',
            ])
        </div>
    </div>
</x-manager-layout>
