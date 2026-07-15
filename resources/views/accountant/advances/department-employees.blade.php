@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

@include('accountant.advances.partials.sub-nav', ['active' => 'requests'])

<x-accountant-layout title="Tạm ứng - {{ $department->department_name }}" subtitle="Nhân viên có yêu cầu ứng lương">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.advances.index') }}" class="text-cyan-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $department->department_code }} · {{ $employees->count() }} nhân viên có tạm ứng</p>
            </div>
            <a href="{{ route('accountant.advances.index') }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'NV có tạm ứng', 'value' => $departmentStats['employees_with_advances']])
            @include('accountant.partials.stat-card', ['label' => 'Chờ duyệt', 'value' => $departmentStats['pending'], 'tone' => 'text-amber-600'])
            @include('accountant.partials.stat-card', ['label' => 'Dư cần trừ', 'value' => $formatMoney($departmentStats['outstanding']), 'tone' => 'text-rose-600'])
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div class="min-w-[180px]">
                <label class="accountant-label">Trạng thái yêu cầu</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\SalaryAdvance::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.advances.index', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-cyan-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Nhân viên tạm ứng</h3>
                <p class="text-xs text-slate-500">Chọn nhân viên để xem và duyệt yêu cầu</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-cyan-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Chức vụ</th>
                            <th class="px-5 py-3 text-center">Số yêu cầu</th>
                            <th class="px-5 py-3 text-center">Chờ duyệt</th>
                            <th class="px-5 py-3 text-right">Dư cần trừ</th>
                            <th class="px-5 py-3">Yêu cầu gần nhất</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-cyan-50/30">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-center font-semibold">{{ $employee->advances_count }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($employee->pending_count > 0)
                                        <span class="inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">
                                            {{ $employee->pending_count }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-bold text-rose-700">
                                    {{ $formatMoney($employee->outstanding_amount) }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($employee->latest_advance)
                                        <span class="accountant-badge {{ $employee->latest_advance->statusBadgeClass() }}">
                                            {{ $employee->latest_advance->statusLabel() }}
                                        </span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $employee->latest_advance->request_date?->format('d/m/Y') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('accountant.advances.index', ['employee_id' => $employee->id]) }}"
                                       class="accountant-btn-secondary !py-1.5 !text-xs">
                                        Xem yêu cầu →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center text-slate-500">
                                    Phòng ban chưa có nhân viên nào có yêu cầu tạm ứng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
