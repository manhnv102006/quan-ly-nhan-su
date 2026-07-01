@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isManager = $roleName === 'manager';

    $navigation = $isManager
        ? \App\Support\ManagerNavigation::items()
        : \App\Support\EmployeeNavigation::items();

    $period = $payroll->payrollPeriod;
    $income = (float) $payroll->basic_salary + (float) $payroll->allowance + (float) $payroll->bonus + (float) ($payroll->overtime_pay ?? 0);
@endphp

<x-staff-layout title="Chi tiết phiếu lương" subtitle="Xem đầy đủ các khoản lương của kỳ đã chọn." :role="$isManager ? 'manager' : 'employee'" :navigation="$navigation">
@php
    $statusColor = match($payroll->displayStatus()) {
        'paid', 'closed'    => 'emerald',
        'approved'          => 'blue',
        'calculated'        => 'violet',
        default             => 'amber',
    };
@endphp
<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.26em] text-sky-600">Payslip Detail</p>
            <h2 class="mt-1.5 text-2xl font-extrabold text-slate-800">Chi tiết phiếu lương</h2>
            <div class="mt-1.5 flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $period?->name ?? 'Kỳ lương' }}
                <span class="text-slate-300">·</span>
                {{ str_pad((string)($period?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $period?->year ?? '—' }}
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employee.payrolls.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Danh sách
            </a>
            <a href="{{ route('employee.payrolls.pdf', $payroll) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700 shadow-sm shadow-sky-500/30 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Tải PDF
            </a>
        </div>
    </div>

    {{-- Hero banner --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 shadow-xl shadow-blue-500/20">
        {{-- Decorative orbs --}}
        <div class="pointer-events-none absolute -top-16 -right-16 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-12 w-48 h-48 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute top-6 right-32 w-24 h-24 rounded-full bg-white/5"></div>

        <div class="relative p-6 lg:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                {{-- Left: main amount --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-sky-200">Thực lĩnh kỳ này</p>
                    <p class="mt-3 text-5xl font-black tracking-tight text-white leading-none">
                        {{ number_format((float) $payroll->total_salary, 0, ',', '.') }}<span class="text-2xl font-bold text-sky-200 ml-1">đ</span>
                    </p>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/15 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                            @if (in_array($payroll->displayStatus(), ['paid','closed']))
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            @endif
                            {{ $payroll->statusLabel() }}
                        </span>
                        <span class="text-sky-200 text-xs font-medium">{{ $period?->name }}</span>
                    </div>
                </div>

                {{-- Right: 3 stat boxes --}}
                <div class="grid grid-cols-3 gap-3 lg:w-[480px]">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-200">Tổng thu nhập</p>
                        <p class="mt-2 text-base font-bold text-white">{{ number_format($income, 0, ',', '.') }}đ</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-200">Khấu trừ</p>
                        <p class="mt-2 text-base font-bold text-white">{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-200">Ngày chi trả</p>
                        <p class="mt-2 text-base font-bold text-white">
                            {{ $period?->paid_at?->format('d/m/Y') ?? $payroll->paid_at?->format('d/m/Y') ?? '—' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Left: Salary breakdown --}}
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Các khoản lương</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Chi tiết các khoản cộng / trừ</p>
                </div>
            </div>

            <div class="p-6 space-y-3">

                {{-- Lương cơ bản --}}
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 group hover:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Lương cơ bản</p>
                            <p class="text-xs text-slate-400">Theo hợp đồng lao động</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-slate-800">{{ number_format((float) $payroll->basic_salary, 0, ',', '.') }}đ</span>
                </div>

                {{-- Phụ cấp --}}
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-5 py-4 hover:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <svg class="text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Phụ cấp</p>
                            <p class="text-xs text-slate-400">Chuyên cần & hỗ trợ</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-slate-800">{{ number_format((float) $payroll->allowance, 0, ',', '.') }}đ</span>
                </div>

                {{-- Thưởng KPI --}}
                <div class="flex items-center justify-between rounded-2xl bg-emerald-50 px-5 py-4 hover:bg-emerald-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <svg class="text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-emerald-700">Thưởng KPI</p>
                            <p class="text-xs text-emerald-500">Dựa trên điểm hiệu suất</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-emerald-600">+{{ number_format((float) $payroll->bonus, 0, ',', '.') }}đ</span>
                </div>

                @if ((float) ($payroll->overtime_pay ?? 0) > 0)
                <div class="flex items-center justify-between rounded-2xl bg-amber-50 px-5 py-4 hover:bg-amber-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <svg class="text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-700">Lương tăng ca</p>
                            <p class="text-xs text-amber-600">{{ number_format((float) ($payroll->overtime_hours ?? 0), 1) }} giờ OT (x1.5)</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-amber-700">+{{ number_format((float) $payroll->overtime_pay, 0, ',', '.') }}đ</span>
                </div>
                @endif

                {{-- Khấu trừ --}}
                <div class="flex items-center justify-between rounded-2xl bg-rose-50 px-5 py-4 hover:bg-rose-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <svg class="text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-rose-700">Khấu trừ</p>
                            <p class="text-xs text-rose-400">Đi trễ / vắng mặt</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-rose-600">-{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ</span>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3 py-1">
                    <div class="flex-1 border-t border-dashed border-slate-200"></div>
                    <span class="text-xs text-slate-400 font-medium">Tổng kết</span>
                    <div class="flex-1 border-t border-dashed border-slate-200"></div>
                </div>

                {{-- Thực lĩnh --}}
                <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-5 shadow-md shadow-sky-500/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <svg class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-base font-bold text-white">Thực lĩnh</p>
                    </div>
                    <span class="text-2xl font-black text-white">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</span>
                </div>

            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-5">

            {{-- Employee info --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Thông tin nhân viên</h3>
                </div>
                <div class="p-5">
                    {{-- Avatar --}}
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-sky-300/30">
                            <span class="text-xl font-black text-white">
                                {{ mb_strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 truncate">{{ $employee->full_name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ $employee->employee_code }}</p>
                        </div>
                    </div>
                    {{-- Info rows --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phòng ban</p>
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ $employee->department?->department_name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Chức vụ</p>
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ $employee->position?->position_name ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Processing timeline --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Tiến trình xử lý</h3>
                </div>
                <div class="p-5">
                    @php
                        $currentStatus = $payroll->displayStatus();
                        $steps = [
                            ['key' => 'calculated', 'label' => 'Đã tính lương',  'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                            ['key' => 'approved',   'label' => 'Đã duyệt',       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',  'meta' => ($period?->approver?->name ?? $payroll->approver?->name), 'date' => ($period?->approved_at ?? $payroll->approved_at)?->format('d/m/Y H:i')],
                            ['key' => 'paid',       'label' => 'Đã chi trả',     'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'meta' => ($period?->payer?->name ?? $payroll->payer?->name), 'date' => ($period?->paid_at ?? $payroll->paid_at)?->format('d/m/Y')],
                        ];
                        $order = ['open'=>0,'calculated'=>1,'approved'=>2,'paid'=>3,'closed'=>3];
                        $currentOrder = $order[$currentStatus] ?? 0;
                    @endphp
                    <div class="space-y-1">
                        @foreach ($steps as $i => $step)
                            @php
                                $stepOrder = $order[$step['key']] ?? 0;
                                $done = $currentOrder >= $stepOrder;
                                $isLast = $i === count($steps) - 1;
                            @endphp
                            <div class="flex gap-3">
                                {{-- Line + dot --}}
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 {{ $done ? 'bg-sky-500 shadow-sm shadow-sky-300' : 'bg-slate-100' }}">
                                        <svg class="w-3.5 h-3.5 {{ $done ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                                        </svg>
                                    </div>
                                    @if (!$isLast)
                                        <div class="w-px flex-1 mt-1 {{ $done ? 'bg-sky-200' : 'bg-slate-100' }}" style="min-height:20px"></div>
                                    @endif
                                </div>
                                {{-- Content --}}
                                <div class="pb-4 min-w-0 flex-1">
                                    <p class="text-sm font-semibold {{ $done ? 'text-slate-800' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                                    @if ($done && !empty($step['meta']))
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $step['meta'] }}</p>
                                    @endif
                                    @if ($done && !empty($step['date']))
                                        <p class="text-xs text-sky-500 font-medium mt-0.5">{{ $step['date'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Status badge --}}
                    <div class="mt-2 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Trạng thái hiện tại</span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $payroll->statusBadgeClass() }}">
                            {{ $payroll->statusLabel() }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</x-staff-layout>
