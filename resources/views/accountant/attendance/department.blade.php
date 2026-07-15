<x-accountant-layout title="Chấm công - {{ $department->department_name }}" subtitle="Ngày công theo tháng">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.attendance.index') }}" class="text-emerald-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $employees->count() }} nhân viên</p>
            </div>
            <a href="{{ route('accountant.attendance.index') }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Tháng</label>
                <input type="month" name="month" value="{{ $month }}" class="accountant-field" onchange="this.form.submit()">
            </div>
        </form>

        @php
            $totalDays = $employees->sum('work_days');
            $avgDays = $employees->count() > 0 ? round($totalDays / $employees->count(), 1) : 0;
        @endphp

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Tổng ngày công', 'value' => $totalDays, 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'TB / nhân viên', 'value' => $avgDays])
            @include('accountant.partials.stat-card', ['label' => 'Tháng', 'value' => \Carbon\Carbon::parse($month.'-01')->format('m/Y'), 'tone' => 'text-indigo-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="bg-emerald-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Chức vụ</th>
                            <th class="px-5 py-3 text-center">Ngày công</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800">
                                        {{ $employee->work_days }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-14 text-center text-slate-500">Phòng ban chưa có nhân viên.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
