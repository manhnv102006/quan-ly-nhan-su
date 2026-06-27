@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isManager = $roleName === 'manager';

    $navigation = $isManager
        ? [
            ['label' => 'Dashboard', 'href' => route('manager.dashboard'), 'route' => 'manager.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Tổng quan điều hành'],
            ['label' => 'Nghỉ phép', 'href' => route('manager.leave-requests'), 'route' => 'manager.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Quản lý nghỉ phép'],
            ['label' => 'Phiếu lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lương cá nhân'],
        ]
        : [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
        ];

    $period = $payroll->payrollPeriod;
    $income = (float) $payroll->basic_salary + (float) $payroll->allowance + (float) $payroll->bonus;
@endphp

<x-staff-layout title="Chi tiết phiếu lương" subtitle="Xem đầy đủ các khoản lương của kỳ đã chọn." :role="$isManager ? 'manager' : 'employee'" :navigation="$navigation">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Payslip detail</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-800">Chi tiết phiếu lương</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $period?->name ?? 'Kỳ lương' }} · {{ str_pad((string) ($period?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $period?->year ?? '—' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('employee.payrolls.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">← Danh sách phiếu lương</a>
                <a href="{{ route('employee.payrolls.pdf', $payroll) }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-sky-700">Tải PDF</a>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-600 p-6 text-white shadow-xl shadow-sky-500/20">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-sky-100">Thực lĩnh kỳ này</p>
                    <p class="mt-3 text-4xl font-extrabold tracking-tight">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</p>
                    <span class="mt-4 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white">{{ $payroll->statusLabel() }}</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-3 lg:w-[520px]">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-100">Tổng thu nhập</p><p class="mt-2 text-lg font-bold">{{ number_format($income, 0, ',', '.') }}đ</p></div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-100">Khấu trừ</p><p class="mt-2 text-lg font-bold">{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ</p></div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-100">Ngày chi trả</p><p class="mt-2 text-lg font-bold">{{ $period?->paid_at?->format('d/m/Y') ?? $payroll->paid_at?->format('d/m/Y') ?? '—' }}</p></div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="staff-card overflow-hidden xl:col-span-2">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-xl font-bold text-slate-800">Các khoản lương</h3>
                    <p class="mt-1 text-sm text-slate-500">Chi tiết các khoản cộng/trừ để ra số tiền thực lĩnh.</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-600">Lương cơ bản</span><span class="font-bold text-slate-800">{{ number_format((float) $payroll->basic_salary, 0, ',', '.') }}đ</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-600">Phụ cấp</span><span class="font-bold text-slate-800">{{ number_format((float) $payroll->allowance, 0, ',', '.') }}đ</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3"><span class="font-medium text-emerald-700">Thưởng KPI</span><span class="font-bold text-emerald-700">+{{ number_format((float) $payroll->bonus, 0, ',', '.') }}đ</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-rose-50 px-4 py-3"><span class="font-medium text-rose-700">Khấu trừ</span><span class="font-bold text-rose-700">-{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ</span></div>
                        <div class="flex items-center justify-between rounded-3xl border border-sky-100 bg-sky-50 px-5 py-4"><span class="text-lg font-extrabold text-sky-700">Thực lĩnh</span><span class="text-2xl font-extrabold text-sky-700">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</span></div>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="staff-card p-6">
                    <h3 class="text-lg font-bold text-slate-800">Thông tin nhân viên</h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Mã nhân viên</p><p class="mt-1 font-semibold text-slate-800">{{ $employee->employee_code }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Họ tên</p><p class="mt-1 font-semibold text-slate-800">{{ $employee->full_name }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Phòng ban / Chức vụ</p><p class="mt-1 font-semibold text-slate-800">{{ $employee->department?->department_name ?? '—' }}</p><p class="mt-1 text-slate-500">{{ $employee->position?->position_name ?? '—' }}</p></div>
                    </div>
                </section>

                <section class="staff-card p-6">
                    <h3 class="text-lg font-bold text-slate-800">Thông tin xử lý</h3>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Trạng thái</span><span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $payroll->statusBadgeClass() }}">{{ $payroll->statusLabel() }}</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Người duyệt</span><span class="font-semibold text-slate-800">{{ $period?->approver?->name ?? $payroll->approver?->name ?? '—' }}</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Ngày duyệt</span><span class="font-semibold text-slate-800">{{ $period?->approved_at?->format('d/m/Y H:i') ?? $payroll->approved_at?->format('d/m/Y H:i') ?? '—' }}</span></div>
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"><span class="text-slate-500">Người chi trả</span><span class="font-semibold text-slate-800">{{ $period?->payer?->name ?? $payroll->payer?->name ?? '—' }}</span></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-staff-layout>
