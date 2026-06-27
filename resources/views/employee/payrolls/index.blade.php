@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isAdmin = $roleName === 'admin';
    $isManager = $roleName === 'manager';

    $navigation = [];
    if ($isManager) {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('manager.dashboard'), 'route' => 'manager.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Tổng quan điều hành'],
            ['label' => 'Nghỉ phép', 'href' => route('manager.leave-requests'), 'route' => 'manager.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Quản lý nghỉ phép'],
            ['label' => 'Phiếu lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lương cá nhân'],
        ];
    } else {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
        ];
    }

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
    $backRoute = $isAdmin ? route('admin.dashboard') : ($isManager ? route('manager.dashboard') : route('employee.dashboard'));
    $layoutParams = $isAdmin
        ? ['title' => 'Phiếu lương của tôi']
        : [
            'title' => 'Phiếu lương của tôi',
            'subtitle' => 'Xem lịch sử và tải phiếu lương dành cho bạn.',
            'role' => $isManager ? 'manager' : 'employee',
            'navigation' => $navigation,
        ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Payslip</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-800">Phiếu lương của tôi</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Theo dõi chi tiết lương, trạng thái chi trả và tải phiếu lương PDF.
                </p>
            </div>
            <a href="{{ $backRoute }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                ← Quay về dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Tổng phiếu</p>
                <p class="mt-3 text-3xl font-extrabold text-slate-800">{{ number_format($summary['count']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Phiếu lương đã phát sinh</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-500">Đã thanh toán</p>
                <p class="mt-3 text-3xl font-extrabold text-emerald-600">{{ number_format($summary['paid_count']) }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ number_format((float) $summary['total_paid'], 0, ',', '.') }}đ đã chi trả</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-500">Kỳ mới nhất</p>
                <p class="mt-3 text-2xl font-extrabold text-slate-800">{{ $summary['latest']?->payrollPeriod?->name ?? 'Chưa có' }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $summary['latest'] ? number_format((float) $summary['latest']->total_salary, 0, ',', '.') . 'đ' : 'Chưa phát sinh phiếu lương' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Phụ cấp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thưởng</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Khấu trừ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thực lĩnh</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payrolls as $payroll)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $payroll->payrollPeriod?->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ str_pad((string) ($payroll->payrollPeriod?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $payroll->payrollPeriod?->year ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 text-sm font-medium">{{ number_format((float) $payroll->basic_salary, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-slate-700 text-sm">{{ number_format((float) $payroll->allowance, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-emerald-600 text-sm font-semibold">+{{ number_format((float) $payroll->bonus, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-rose-600 text-sm font-semibold">-{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-slate-800 font-bold">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-semibold {{ $payroll->statusBadgeClass() }}">
                                        {{ $payroll->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('employee.payrolls.pdf', $payroll) }}" class="inline-flex items-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-700">
                                        Tải PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400 text-sm">
                                    Bạn chưa có phiếu lương nào trong hệ thống.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payrolls->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
