@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $formatPct = fn ($n) => rtrim(rtrim(number_format((float) $n * 100, 2, ',', '.'), '0'), ',') . '%';
    $department = $employee->department;
@endphp

<x-accountant-layout title="Bảo hiểm - {{ $employee->full_name }}" subtitle="Hồ sơ tham gia BHXH · BHYT · BHTN">
    @include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])
    <div class="accountant-page">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800">{{ session('info') }}</div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.insurance.index') }}" class="text-sky-700 hover:underline">Phòng ban</a>
                    @if($department)
                        <span>/</span>
                        <a href="{{ route('accountant.insurance.index', ['department_id' => $department->id]) }}" class="text-sky-700 hover:underline">{{ $department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-700">{{ $employee->full_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                    · {{ $department?->department_name ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($department)
                    <a href="{{ route('accountant.insurance.index', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">← Nhân viên</a>
                @endif
                @if($insurance)
                    <a href="{{ route('accountant.insurance.edit', $insurance) }}" class="accountant-btn-primary">Sửa hồ sơ</a>
                @elseif($employee->status === 'active')
                    <a href="{{ route('accountant.insurance.create', ['employee_id' => $employee->id]) }}" class="accountant-btn-primary">+ Thêm hồ sơ BH</a>
                @endif
            </div>
        </div>

        @if($insurance)
            @if($employee->status === 'resigned' && $insurance->status === 'active')
                <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4">
                    <p class="text-sm font-bold text-rose-800">Nhân viên đã nghỉ việc nhưng hồ sơ BH vẫn đang đóng</p>
                    <form method="POST" action="{{ route('accountant.insurance.stop-resigned', $employee) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="accountant-btn-secondary !text-xs text-rose-700">Ngừng đóng BH</button>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @include('accountant.partials.stat-card', ['label' => 'NLĐ đóng/tháng', 'value' => $formatMoney($contributions['total_employee']), 'tone' => 'text-sky-600'])
                @include('accountant.partials.stat-card', ['label' => 'DN đóng/tháng', 'value' => $formatMoney($contributions['total_employer']), 'tone' => 'text-indigo-600'])
                @include('accountant.partials.stat-card', ['label' => 'Lương đóng BH', 'value' => $formatMoney($insurance->contribution_salary), 'tone' => 'text-slate-700'])
                @include('accountant.partials.stat-card', ['label' => 'Trạng thái', 'value' => $insurance->statusLabel(), 'tone' => 'text-emerald-700'])
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="accountant-card p-5">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">Thông tin hồ sơ</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Số sổ BHXH</dt>
                            <dd class="font-semibold text-slate-800">{{ $insurance->social_insurance_number ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Mã BHYT</dt>
                            <dd class="font-semibold text-slate-800">{{ $insurance->health_insurance_code ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Ngày bắt đầu</dt>
                            <dd class="font-semibold text-slate-800">{{ $insurance->start_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Ngày kết thúc</dt>
                            <dd class="font-semibold text-slate-800">{{ $insurance->end_date?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Trạng thái</dt>
                            <dd><span class="accountant-badge {{ $insurance->statusBadgeClass() }}">{{ $insurance->statusLabel() }}</span></dd>
                        </div>
                    </dl>
                    @if($insurance->note)
                        <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <p class="text-xs font-bold uppercase text-slate-500">Ghi chú</p>
                            <p class="mt-1">{{ $insurance->note }}</p>
                        </div>
                    @endif
                    @if($insurance->stop_reason)
                        <div class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="text-xs font-bold uppercase">Lý do ngừng đóng</p>
                            <p class="mt-1">{{ $insurance->stop_reason }}</p>
                        </div>
                    @endif
                </div>

                <div class="accountant-card p-5">
                    <h3 class="mb-4 text-sm font-bold text-slate-800">Tỷ lệ &amp; mức đóng hàng tháng</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase text-slate-500">
                                    <th class="pb-2">Loại BH</th>
                                    <th class="pb-2 text-right">Tỷ lệ</th>
                                    <th class="pb-2 text-right">NLĐ</th>
                                    <th class="pb-2 text-right">DN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="py-2 font-medium">BHXH</td>
                                    <td class="py-2 text-right text-xs text-slate-500">{{ $formatPct($insurance->bhxh_employee_rate) }} / {{ $formatPct($insurance->bhxh_employer_rate) }}</td>
                                    <td class="py-2 text-right text-sky-700">{{ $formatMoney($contributions['bhxh_employee']) }}</td>
                                    <td class="py-2 text-right text-indigo-700">{{ $formatMoney($contributions['bhxh_employer']) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 font-medium">BHYT</td>
                                    <td class="py-2 text-right text-xs text-slate-500">{{ $formatPct($insurance->bhyt_employee_rate) }} / {{ $formatPct($insurance->bhyt_employer_rate) }}</td>
                                    <td class="py-2 text-right text-sky-700">{{ $formatMoney($contributions['bhyt_employee']) }}</td>
                                    <td class="py-2 text-right text-indigo-700">{{ $formatMoney($contributions['bhyt_employer']) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2 font-medium">BHTN</td>
                                    <td class="py-2 text-right text-xs text-slate-500">{{ $formatPct($insurance->bhtn_employee_rate) }}</td>
                                    <td class="py-2 text-right text-sky-700">{{ $formatMoney($contributions['bhtn_employee']) }}</td>
                                    <td class="py-2 text-right text-indigo-700">{{ $formatMoney($contributions['bhtn_employer']) }}</td>
                                </tr>
                                <tr class="font-bold">
                                    <td class="pt-3">Tổng cộng</td>
                                    <td class="pt-3"></td>
                                    <td class="pt-3 text-right text-sky-800">{{ $formatMoney($contributions['total_employee']) }}</td>
                                    <td class="pt-3 text-right text-indigo-800">{{ $formatMoney($contributions['total_employer']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="accountant-card px-6 py-16 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-50 text-2xl">🛡️</div>
                <h3 class="text-lg font-bold text-slate-800">Chưa có hồ sơ bảo hiểm</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                    Nhân viên <strong>{{ $employee->full_name }}</strong> chưa được đăng ký tham gia BHXH, BHYT, BHTN.
                </p>
                @if($employee->status === 'active')
                    <a href="{{ route('accountant.insurance.create', ['employee_id' => $employee->id]) }}" class="accountant-btn-primary mt-6 inline-flex">
                        + Thêm hồ sơ BH
                    </a>
                @else
                    <p class="mt-4 text-sm text-amber-700">Nhân viên không còn hoạt động — không thể tạo hồ sơ mới.</p>
                @endif
            </div>
        @endif
    </div>
</x-accountant-layout>
