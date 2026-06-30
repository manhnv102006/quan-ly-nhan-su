@php
    $user = Auth::user();
    $roleName = $user->role?->name;
    $isAdmin = $roleName === 'admin';
    $isManager = $roleName === 'manager';

    $navigation = [];
    if ($isManager) {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('manager.dashboard'), 'route' => 'manager.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Tổng quan điều hành'],
            ['label' => 'Nghỉ phép', 'href' => route('manager.leave-requests.index'), 'route' => 'manager.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Quản lý nghỉ phép'],
            ['label' => 'Phiếu lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lương cá nhân'],
        ];
    } else {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
        ];
    }

    if (! $isManager && ! $isAdmin) {
        $navigation = [
            ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
            ['label' => 'Chấm công', 'href' => route('employee.dashboard') . '#attendance', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Lịch sử gần đây'],
            ['label' => 'KPI', 'href' => route('employee.dashboard') . '#kpi', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'note' => 'Mục tiêu công việc'],
            ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls.*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Phiếu lương của bạn'],
            ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
            ['label' => 'Thông báo', 'href' => route('employee.dashboard') . '#notices', 'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'note' => 'Tin nội bộ'],
            ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
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

        {{-- Page header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Payslip</p>
                <h2 class="mt-1.5 text-2xl font-extrabold text-slate-800">Phiếu lương của tôi</h2>
                <p class="text-sm text-slate-500 mt-1">Theo dõi chi tiết lương, trạng thái chi trả và tải phiếu lương PDF.</p>
            </div>
            <a href="{{ $backRoute }}"
               class="self-start sm:self-auto inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold text-xs hover:bg-slate-50 hover:text-slate-800 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Quay về dashboard
            </a>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- Tổng phiếu --}}
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Tổng phiếu</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-800 leading-none">{{ number_format($summary['count']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Phiếu lương đã phát sinh</p>
                </div>
            </div>

            {{-- Đã thanh toán --}}
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">Đã thanh toán</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-600 leading-none">{{ number_format($summary['paid_count']) }}</p>
                    <p class="mt-1 text-xs text-slate-500 truncate">{{ number_format((float) $summary['total_paid'], 0, ',', '.') }}đ đã chi trả</p>
                </div>
            </div>

            {{-- Kỳ mới nhất --}}
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-sky-500">Kỳ mới nhất</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-800 leading-tight truncate">
                        {{ $summary['latest']?->payrollPeriod?->name ?? 'Chưa có' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $summary['latest'] ? number_format((float) $summary['latest']->total_salary, 0, ',', '.') . 'đ' : 'Chưa phát sinh phiếu lương' }}
                    </p>
                </div>
            </div>

        </div>

        {{-- Filter card --}}
        <div class="rounded-3xl shadow-sm overflow-hidden border border-sky-100">

            {{-- Gradient header --}}
            <div class="bg-gradient-to-r from-sky-500 to-sky-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">Bộ lọc phiếu lương</p>
                        <p class="text-xs text-sky-100 mt-0.5">Lọc theo năm, tháng hoặc trạng thái kỳ lương</p>
                    </div>
                </div>
                @if ($filterYear || $filterMonth || $filterStatus)
                    <a href="{{ route('employee.payrolls.index') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/20 text-white text-xs font-semibold hover:bg-white/30 transition border border-white/30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Xóa bộ lọc
                    </a>
                @endif
            </div>

            {{-- Form body --}}
            <form method="GET" action="{{ route('employee.payrolls.index') }}"
                  class="bg-white px-6 py-5">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    {{-- Năm --}}
                    <div class="group">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Năm
                        </label>
                        <div class="relative">
                            <select name="year"
                                    class="w-full h-12 pl-4 pr-11 rounded-2xl border-2 text-sm font-medium transition appearance-none cursor-pointer
                                           {{ $filterYear
                                               ? 'border-sky-400 bg-sky-50 text-sky-700 shadow-sm shadow-sky-100'
                                               : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-300 focus:border-sky-400 focus:bg-white focus:shadow-sm focus:shadow-sky-100' }}
                                           focus:outline-none focus:ring-0"
                                    style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 14px center;">
                                <option value="">Tất cả năm</option>
                                @foreach ($payrollYears as $yr)
                                    <option value="{{ $yr }}" @selected($filterYear == $yr)>{{ $yr }}</option>
                                @endforeach
                            </select>
                            @if ($filterYear)
                                <span class="absolute right-9 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-sky-500"></span>
                            @endif
                        </div>
                    </div>

                    {{-- Tháng --}}
                    <div class="group">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tháng
                        </label>
                        <div class="relative">
                            <select name="month"
                                    class="w-full h-12 pl-4 pr-11 rounded-2xl border-2 text-sm font-medium transition appearance-none cursor-pointer
                                           {{ $filterMonth
                                               ? 'border-sky-400 bg-sky-50 text-sky-700 shadow-sm shadow-sky-100'
                                               : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-300 focus:border-sky-400 focus:bg-white focus:shadow-sm focus:shadow-sky-100' }}
                                           focus:outline-none focus:ring-0"
                                    style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 14px center;">
                                <option value="">Tất cả tháng</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected($filterMonth == $m)>Tháng {{ $m }}</option>
                                @endforeach
                            </select>
                            @if ($filterMonth)
                                <span class="absolute right-9 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-sky-500"></span>
                            @endif
                        </div>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="group">
                        <label class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Trạng thái
                        </label>
                        <div class="relative">
                            <select name="status"
                                    class="w-full h-12 pl-4 pr-11 rounded-2xl border-2 text-sm font-medium transition appearance-none cursor-pointer
                                           {{ $filterStatus
                                               ? 'border-sky-400 bg-sky-50 text-sky-700 shadow-sm shadow-sky-100'
                                               : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-300 focus:border-sky-400 focus:bg-white focus:shadow-sm focus:shadow-sky-100' }}
                                           focus:outline-none focus:ring-0"
                                    style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2338bdf8' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 14px center;">
                                <option value="">Tất cả trạng thái</option>
                                <option value="open"       @selected($filterStatus === 'open')>Đang mở</option>
                                <option value="calculated" @selected($filterStatus === 'calculated')>Đã tính lương</option>
                                <option value="approved"   @selected($filterStatus === 'approved')>Đã duyệt</option>
                                <option value="paid"       @selected($filterStatus === 'paid')>Đã thanh toán</option>
                                <option value="closed"     @selected($filterStatus === 'closed')>Đã đóng</option>
                            </select>
                            @if ($filterStatus)
                                <span class="absolute right-9 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-sky-500"></span>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Footer: active tags + submit --}}
                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-3">

                    {{-- Active tags (có nút × xóa từng cái) --}}
                    @if ($filterYear || $filterMonth || $filterStatus)
                        @php
                            $statusLabels = ['open'=>'Đang mở','calculated'=>'Đã tính lương','approved'=>'Đã duyệt','paid'=>'Đã thanh toán','closed'=>'Đã đóng'];
                            $buildUrl = fn($except) => route('employee.payrolls.index', array_filter([
                                'year'   => $except !== 'year'   ? $filterYear   : null,
                                'month'  => $except !== 'month'  ? $filterMonth  : null,
                                'status' => $except !== 'status' ? $filterStatus : null,
                            ]));
                        @endphp
                        <div class="flex flex-wrap items-center gap-2 flex-1">
                            <span class="text-xs text-slate-400 font-medium">Đang lọc:</span>
                            @if ($filterYear)
                                <a href="{{ $buildUrl('year') }}"
                                   class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-1 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold hover:bg-sky-200 transition">
                                    Năm {{ $filterYear }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                            @if ($filterMonth)
                                <a href="{{ $buildUrl('month') }}"
                                   class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-1 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold hover:bg-sky-200 transition">
                                    Tháng {{ $filterMonth }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                            @if ($filterStatus)
                                <a href="{{ $buildUrl('status') }}"
                                   class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-1 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold hover:bg-sky-200 transition">
                                    {{ $statusLabels[$filterStatus] ?? $filterStatus }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="flex-1 text-xs text-slate-400">Chọn tiêu chí lọc bên trên rồi bấm áp dụng.</p>
                    @endif

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-sky-600 text-white text-sm font-bold shadow-md shadow-sky-500/25 hover:bg-sky-700 hover:shadow-sky-500/40 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Áp dụng bộ lọc
                    </button>
                </div>
            </form>
        </div>

        {{-- Table card --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">

            {{-- Table header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-800">Danh sách phiếu lương</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if ($filterYear || $filterMonth || $filterStatus)
                            <span class="font-semibold text-sky-600">{{ $payrolls->total() }}</span> kết quả phù hợp
                        @else
                            Tổng cộng {{ $payrolls->total() }} phiếu
                        @endif
                    </p>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Kỳ lương</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Lương cơ bản</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Phụ cấp</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Thưởng</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Khấu trừ</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Thực lĩnh</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Trạng thái</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($payrolls as $payroll)
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $payroll->payrollPeriod?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium">
                                        {{ str_pad((string) ($payroll->payrollPeriod?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $payroll->payrollPeriod?->year ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-slate-600 text-sm">{{ number_format((float) $payroll->basic_salary, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-right text-slate-600 text-sm">{{ number_format((float) $payroll->allowance, 0, ',', '.') }}đ</td>
                                <td class="px-6 py-4 text-right text-emerald-600 text-sm font-semibold">
                                    @if ($payroll->bonus > 0)+{{ number_format((float) $payroll->bonus, 0, ',', '.') }}đ@else <span class="text-slate-300">—</span>@endif
                                </td>
                                <td class="px-6 py-4 text-right text-rose-500 text-sm font-semibold">
                                    @if ($payroll->deduction > 0)-{{ number_format((float) $payroll->deduction, 0, ',', '.') }}đ@else <span class="text-slate-300">—</span>@endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-slate-900 font-bold text-sm">{{ number_format((float) $payroll->total_salary, 0, ',', '.') }}đ</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-semibold {{ $payroll->statusBadgeClass() }}">
                                        {{ $payroll->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('employee.payrolls.show', $payroll) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Chi tiết
                                        </a>
                                        <a href="{{ route('employee.payrolls.pdf', $payroll) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-700 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                            </svg>
                                            Tải PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-500">
                                                @if ($filterYear || $filterMonth || $filterStatus)
                                                    Không có phiếu lương nào khớp với bộ lọc
                                                @else
                                                    Bạn chưa có phiếu lương nào
                                                @endif
                                            </p>
                                            @if ($filterYear || $filterMonth || $filterStatus)
                                                <a href="{{ route('employee.payrolls.index') }}"
                                                   class="mt-2 inline-block text-xs text-sky-600 hover:underline">
                                                    Xóa bộ lọc để xem tất cả
                                                </a>
                                            @endif
                                        </div>
                                    </div>
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
