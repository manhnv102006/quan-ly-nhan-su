@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-accountant-layout title="Nhân viên - {{ $department->department_name }}" subtitle="Chọn nhân viên để xem hồ sơ BH">
<div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.insurance.index') }}" class="text-sky-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $department->department_code }} · {{ $departmentStats['total'] }} nhân viên</p>
            </div>
            <a href="{{ route('accountant.insurance.index') }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Nhân viên', 'value' => $departmentStats['total']])
            @include('accountant.partials.stat-card', ['label' => 'Đang đóng BH', 'value' => $departmentStats['active'], 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Chưa có hồ sơ', 'value' => $departmentStats['no_profile'], 'tone' => 'text-rose-600'])
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            <div class="min-w-[200px] flex-1">
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div class="min-w-[180px]">
                <label class="accountant-label">Trạng thái BH</label>
                <select name="insurance_status" class="accountant-field">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(($filters['insurance_status'] ?? '') === 'active')>Đang đóng</option>
                    <option value="suspended" @selected(($filters['insurance_status'] ?? '') === 'suspended')>Tạm dừng</option>
                    <option value="stopped" @selected(($filters['insurance_status'] ?? '') === 'stopped')>Đã ngừng</option>
                    <option value="none" @selected(($filters['insurance_status'] ?? '') === 'none')>Chưa có hồ sơ</option>
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.insurance.index', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-sky-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Danh sách nhân viên</h3>
                <p class="text-xs text-slate-500">Chọn nhân viên để xem hoặc tạo hồ sơ bảo hiểm</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-sky-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Chức vụ</th>
                            <th class="px-5 py-3 text-center">Trạng thái BH</th>
                            <th class="px-5 py-3 text-right">Lương đóng BH</th>
                            <th class="px-5 py-3 text-right">NLĐ đóng/tháng</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            @php
                                $profile = $employee->insurance;
                                $contrib = $employee->insurance_contributions ?? null;
                            @endphp
                            <tr class="hover:bg-sky-50/30">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                    @if($employee->status === 'resigned' && $profile?->status === 'active')
                                        <span class="mt-1 inline-block text-xs font-bold text-rose-600">NV đã nghỉ việc</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($profile)
                                        <span class="accountant-badge {{ $profile->statusBadgeClass() }}">{{ $profile->statusLabel() }}</span>
                                    @else
                                        <span class="accountant-badge bg-slate-100 text-slate-600">Chưa có hồ sơ</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-medium">
                                    {{ $profile ? $formatMoney($profile->contribution_salary) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right text-sky-700">
                                    {{ $contrib ? $formatMoney($contrib['total_employee']) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('accountant.insurance.index', ['employee_id' => $employee->id]) }}"
                                       class="accountant-btn-secondary !py-1.5 !text-xs">
                                        {{ $profile ? 'Xem BH →' : 'Tạo hồ sơ →' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-slate-500">Không có nhân viên phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
