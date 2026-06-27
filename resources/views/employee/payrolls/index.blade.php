@php
    $user = Auth::user();
    $isAdmin = $user->role->name === 'admin';
    $isManager = $user->role->name === 'manager';

    $navigation = [];
    if ($isManager) {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('manager.dashboard'), 'route' => 'manager.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Tổng quan điều hành'],
            ['label' => 'Nghỉ phép', 'href' => route('manager.leave-requests'), 'route' => 'manager.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Quản lý nghỉ phép'],
        ];
    } else {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.index', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
        ];
    }

    $layout = $isAdmin ? 'admin-layout' : 'staff-layout';
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
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Phiếu lương của tôi</h2>
                <p class="text-xs text-slate-500 mt-1">Danh sách các kỳ lương đã được tính và có sẵn cho bạn.</p>
            </div>
            <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition">
                ← Quay về dashboard
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lương cơ bản</th>
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
                                <td class="px-6 py-4 text-slate-800 font-bold">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-semibold {{ $payroll->payrollPeriod?->isPaid() ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($payroll->payrollPeriod?->isApproved() ? 'bg-sky-50 text-sky-700 border-sky-100' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                        {{ $payroll->payrollPeriod?->isPaid() ? 'Đã chi trả' : ($payroll->payrollPeriod?->isApproved() ? 'Đã duyệt' : 'Chờ xử lý') }}
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
                                <td colspan="5" class="text-center py-12 text-slate-400 text-sm">
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
