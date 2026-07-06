<x-admin-layout title="Dashboard">
    @php
        $employeeTotal = max(1, array_sum($employeeStatus));
        $statusSegments = [
            ['key' => 'active', 'label' => 'Đang làm việc', 'color' => 'bg-violet-500', 'text' => 'text-violet-700', 'bg' => 'bg-violet-50'],
            ['key' => 'inactive', 'label' => 'Tạm ngưng', 'color' => 'bg-amber-400', 'text' => 'text-amber-700', 'bg' => 'bg-amber-50'],
            ['key' => 'resigned', 'label' => 'Đã nghỉ', 'color' => 'bg-rose-400', 'text' => 'text-rose-700', 'bg' => 'bg-rose-50'],
        ];
        $queueIcons = [
            'leave' => ['emoji' => '🏖️', 'tone' => 'from-amber-400 to-orange-500'],
            'overtime' => ['emoji' => '⏱️', 'tone' => 'from-violet-400 to-indigo-500'],
            'kpi' => ['emoji' => '🎯', 'tone' => 'from-cyan-400 to-blue-500'],
        ];
        $payrollStages = [
            ['key' => 'open', 'label' => 'Mở kỳ', 'color' => 'bg-slate-400'],
            ['key' => 'calculated', 'label' => 'Đã tính', 'color' => 'bg-amber-400'],
            ['key' => 'approved', 'label' => 'Đã duyệt', 'color' => 'bg-violet-500'],
            ['key' => 'paid', 'label' => 'Đã trả', 'color' => 'bg-emerald-500'],
        ];
        $quickActions = [
            ['route' => 'admin.employees', 'label' => 'Nhân viên', 'desc' => 'Hồ sơ & phòng ban', 'emoji' => '👥', 'tone' => 'from-violet-500 to-purple-600'],
            ['route' => 'admin.payroll-periods.index', 'label' => 'Kỳ lương', 'desc' => 'Tính & chi trả', 'emoji' => '💰', 'tone' => 'from-emerald-500 to-teal-600'],
            ['route' => 'admin.attendances', 'label' => 'Chấm công', 'desc' => 'Ca & báo cáo', 'emoji' => '⏰', 'tone' => 'from-cyan-500 to-blue-600'],
            ['route' => 'admin.kpi-assignments.index', 'label' => 'Giao KPI', 'desc' => 'Mục tiêu quản lý', 'emoji' => '📊', 'tone' => 'from-amber-500 to-orange-600'],
            ['route' => 'admin.leave-requests', 'label' => 'Nghỉ phép', 'desc' => 'Duyệt quản lý', 'emoji' => '📋', 'tone' => 'from-indigo-500 to-violet-600'],
            ['route' => 'admin.recruitment', 'label' => 'Tuyển dụng', 'desc' => 'Ứng viên & PV', 'emoji' => '🎯', 'tone' => 'from-rose-500 to-pink-600'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="relative mb-8 overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-900 via-violet-900 to-indigo-800 p-6 text-white shadow-2xl shadow-violet-900/20 sm:p-8">
        <div class="absolute -right-16 top-0 h-56 w-56 rounded-full bg-violet-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-44 w-44 -translate-x-1/4 translate-y-1/4 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.08),transparent_45%)]"></div>

        <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-300"></span>
                    Trung tâm điều hành nhân sự
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                    Xin chào {{ $firstName }}, hôm nay có {{ number_format($pending['total']) }} việc cần ưu tiên.
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-violet-100/90 sm:text-base">
                    Tổng quan nhân sự, chấm công, phê duyệt và vận hành lương — mọi chỉ số quan trọng được gom về một màn hình.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="#approvals" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-violet-700 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-violet-50">
                        Xử lý phê duyệt
                        @if ($pending['total'] > 0)
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $pending['total'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.payroll-periods.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        Quản lý kỳ lương
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:w-[420px] xl:grid-cols-1">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-200">Hôm nay</p>
                    <p class="mt-2 text-lg font-bold">{{ now()->translatedFormat('l, d/m/Y') }}</p>
                    <p class="mt-1 text-sm text-violet-100/85">{{ number_format($todayAttendance) }} lượt chấm công</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-200">Thông báo chưa đọc</p>
                    <p class="mt-2 text-lg font-bold">{{ number_format($unreadNotifications) }}</p>
                    <p class="mt-1 text-sm text-violet-100/85">Cập nhật nội bộ mới</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-200">Tổng nhân sự</p>
                    <p class="mt-2 text-lg font-bold">{{ number_format($employeeStatus['active']) }} / {{ number_format($employeeTotal) }}</p>
                    <p class="mt-1 text-sm text-violet-100/85">Đang hoạt động</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Hero metrics --}}
    <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($heroMetrics as $metric)
            @php
                $href = ! empty($metric['is_url']) ? $metric['route'] : route($metric['route']);
            @endphp
            <a href="{{ $href }}" class="admin-stat-card group block">
                <div class="flex items-start justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $metric['tone'] }} text-white shadow-lg transition duration-300 group-hover:scale-110">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['icon'] }}" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 transition group-hover:text-violet-500">Chi tiết →</span>
                </div>
                <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($metric['value']) }}</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $metric['label'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $metric['hint'] }}</p>
            </a>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Approval queue --}}
        <section id="approvals" class="admin-card p-6 sm:p-7 xl:col-span-7">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Ưu tiên hôm nay</p>
                    <h3 class="mt-1 text-xl font-bold text-slate-800">Hàng đợi phê duyệt</h3>
                    <p class="mt-1 text-sm text-slate-500">Đơn từ quản lý và KPI chờ xử lý</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($pending['managerLeave'] > 0)
                        <a href="{{ route('admin.leave-requests') }}" class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                            Nghỉ phép {{ $pending['managerLeave'] }}
                        </a>
                    @endif
                    @if ($pending['managerOvertime'] > 0)
                        <a href="{{ route('admin.overtime-requests.index') }}" class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                            Tăng ca {{ $pending['managerOvertime'] }}
                        </a>
                    @endif
                    @if ($pending['kpiAssignments'] > 0)
                        <a href="{{ route('admin.kpi-assignments.index') }}" class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100">
                            KPI {{ $pending['kpiAssignments'] }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($approvalQueue as $item)
                    @php $icon = $queueIcons[$item['type']] ?? $queueIcons['leave']; @endphp
                    <a href="{{ $item['url'] }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-violet-200 hover:bg-white hover:shadow-md hover:shadow-violet-100/50">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $icon['tone'] }} text-lg shadow-md">
                            {{ $icon['emoji'] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold text-slate-800 group-hover:text-violet-700">{{ $item['title'] }}</p>
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">Chờ duyệt</span>
                            </div>
                            <p class="mt-0.5 truncate text-sm text-slate-500">{{ $item['subtitle'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $item['meta'] }} · {{ optional($item['created_at'])->diffForHumans() }}</p>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-10 text-center">
                        <p class="text-3xl">✅</p>
                        <p class="mt-3 font-semibold text-slate-700">Không có đơn chờ duyệt</p>
                        <p class="mt-1 text-sm text-slate-500">Mọi yêu cầu đã được xử lý.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Employee status + department --}}
        <section class="admin-card p-6 sm:p-7 xl:col-span-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Cơ cấu nhân sự</p>
            <h3 class="mt-1 text-xl font-bold text-slate-800">Phân bổ theo trạng thái</h3>

            <div class="mt-6 flex h-4 overflow-hidden rounded-full bg-slate-100">
                @foreach ($statusSegments as $segment)
                    @php $width = round(($employeeStatus[$segment['key']] / $employeeTotal) * 100, 1); @endphp
                    @if ($width > 0)
                        <div class="{{ $segment['color'] }} h-full transition-all" style="width: {{ $width }}%" title="{{ $segment['label'] }}"></div>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                @foreach ($statusSegments as $segment)
                    <div class="rounded-2xl {{ $segment['bg'] }} px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider {{ $segment['text'] }}">{{ $segment['label'] }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($employeeStatus[$segment['key']]) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 border-t border-slate-100 pt-6">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="font-bold text-slate-800">Top phòng ban</h4>
                    <a href="{{ route('admin.departments') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">Xem tất cả</a>
                </div>
                <div class="space-y-3">
                    @forelse ($departmentHeadcount as $department)
                        @php $pct = round(($department->active_employees_count / $maxDepartmentCount) * 100); @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $department->department_name }}</span>
                                <span class="font-bold text-slate-800">{{ number_format($department->active_employees_count) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Chưa có dữ liệu phòng ban.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Attendance trend --}}
        <section class="admin-card p-6 sm:p-7 xl:col-span-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-cyan-600">Xu hướng</p>
                    <h3 class="mt-1 text-xl font-bold text-slate-800">Chấm công 6 tháng gần nhất</h3>
                </div>
                <a href="{{ route('admin.attendance-reports.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">Báo cáo chi tiết →</a>
            </div>

            <div class="flex h-52 items-end gap-3 sm:gap-4">
                @forelse ($monthlyAttendance as $point)
                    @php
                        $barHeight = max(12, round(($point->total / $maxMonthlyAttendance) * 100));
                        $monthLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $point->month_key)->translatedFormat('M');
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-500">{{ number_format($point->total) }}</span>
                        <div class="relative w-full max-w-[3.5rem] flex-1">
                            <div
                                class="absolute bottom-0 w-full rounded-t-2xl bg-gradient-to-t from-violet-600 to-indigo-400 shadow-lg shadow-violet-200/50 transition-all hover:from-violet-500 hover:to-cyan-400"
                                style="height: {{ $barHeight }}%"
                            ></div>
                        </div>
                        <span class="text-xs font-semibold text-slate-600">{{ $monthLabel }}</span>
                    </div>
                @empty
                    <div class="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-slate-200 py-12 text-sm text-slate-500">
                        Chưa có dữ liệu chấm công.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Payroll + recruitment --}}
        <section class="space-y-6 xl:col-span-4">
            <div class="admin-card p-6 sm:p-7">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Lương</p>
                <h3 class="mt-1 text-lg font-bold text-slate-800">Trạng thái kỳ lương</h3>
                <div class="mt-5 space-y-3">
                    @foreach ($payrollStages as $stage)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full {{ $stage['color'] }}"></span>
                                <span class="text-sm font-medium text-slate-700">{{ $stage['label'] }}</span>
                            </div>
                            <span class="text-lg font-extrabold text-slate-800">{{ number_format($payrollSnapshot[$stage['key']]) }}</span>
                        </div>
                    @endforeach
                </div>
                @if ($pending['payroll'] > 0)
                    <a href="{{ route('admin.payroll-periods.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        {{ $pending['payroll'] }} kỳ cần xử lý
                    </a>
                @endif
            </div>

            <div class="admin-card p-6 sm:p-7">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-rose-600">Tuyển dụng</p>
                <h3 class="mt-1 text-lg font-bold text-slate-800">Pipeline hiện tại</h3>
                <div class="mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-2xl bg-rose-50 p-3 text-center">
                        <p class="text-2xl font-extrabold text-rose-700">{{ number_format($recruitmentSnapshot['new_candidates']) }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-rose-600">Ứng viên mới</p>
                    </div>
                    <div class="rounded-2xl bg-indigo-50 p-3 text-center">
                        <p class="text-2xl font-extrabold text-indigo-700">{{ number_format($recruitmentSnapshot['open_jobs']) }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-indigo-600">Tin đang mở</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-3 text-center">
                        <p class="text-2xl font-extrabold text-amber-700">{{ number_format($recruitmentSnapshot['pending_interviews']) }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-amber-600">PV chờ KQ</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Quick actions --}}
        <section class="admin-card p-6 sm:p-7 xl:col-span-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Lối tắt</p>
            <h3 class="mt-1 text-xl font-bold text-slate-800">Truy cập nhanh module</h3>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($quickActions as $action)
                    <a href="{{ route($action['route']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition hover:border-violet-200 hover:bg-white hover:shadow-md hover:shadow-violet-100/50">
                        <div class="absolute inset-0 bg-gradient-to-br {{ $action['tone'] }} opacity-0 transition group-hover:opacity-[0.06]"></div>
                        <div class="relative flex items-start gap-3">
                            <span class="text-2xl">{{ $action['emoji'] }}</span>
                            <div>
                                <p class="font-bold text-slate-800 group-hover:text-violet-700">{{ $action['label'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $action['desc'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Recent employees --}}
        <section class="admin-card p-6 sm:p-7 xl:col-span-4">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Nhân sự mới</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-800">Tuyển dụng gần đây</h3>
                </div>
                <a href="{{ route('admin.employees') }}" class="text-xs font-semibold text-violet-600">Xem tất cả</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentEmployees as $employee)
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50/80 px-4 py-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-bold text-white">
                            {{ strtoupper(mb_substr($employee->full_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-800">{{ $employee->full_name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $employee->department?->department_name ?? '—' }} · {{ $employee->position?->position_name ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 text-[11px] font-medium text-slate-400">{{ optional($employee->hire_date)->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Chưa có nhân viên.</p>
                @endforelse
            </div>
        </section>

        {{-- Expiring contracts --}}
        <section class="admin-card p-6 sm:p-7 xl:col-span-3">
            <div class="mb-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-rose-600">Cảnh báo</p>
                <h3 class="mt-1 text-lg font-bold text-slate-800">Hợp đồng sắp hết hạn</h3>
            </div>
            <div class="space-y-3">
                @forelse ($expiringContracts as $contract)
                    <a href="{{ route('admin.contracts.show', $contract) }}" class="block rounded-2xl border border-rose-100 bg-rose-50/60 px-4 py-3 transition hover:bg-rose-50">
                        <p class="truncate font-semibold text-rose-900">{{ $contract->employee?->full_name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs text-rose-700">Hết hạn {{ optional($contract->end_date)->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center">
                        <p class="text-2xl">🛡️</p>
                        <p class="mt-2 text-sm font-medium text-slate-600">Không có hợp đồng sắp hết hạn</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Module stats strip --}}
    <section class="mt-6 admin-card p-5 sm:p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Tổng quan module</h3>
            <span class="text-xs text-slate-500">Cập nhật theo thời gian thực khi tải trang</span>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($moduleStats as $stat)
                <a href="{{ route($stat['route']) }}" class="rounded-2xl {{ $stat['tone'] }} px-4 py-4 text-center transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-2xl font-extrabold">{{ number_format($stat['value']) }}</p>
                    <p class="mt-1 text-xs font-semibold opacity-80">{{ $stat['label'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Open job posts --}}
    @if ($recentJobs->isNotEmpty())
        <section class="mt-6 admin-card p-6 sm:p-7">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Tuyển dụng</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-800">Tin tuyển dụng mới nhất</h3>
                </div>
                <a href="{{ route('admin.recruitment.job-posts') }}" class="text-xs font-semibold text-violet-600">Quản lý tin →</a>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($recentJobs as $job)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-slate-800 line-clamp-2">{{ $job->title }}</p>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $job->status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $job->status === 'open' ? 'Đang mở' : 'Đã đóng' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ $job->department?->department_name ?? '—' }}</p>
                        <p class="mt-3 text-sm font-bold text-violet-700">{{ number_format($job->quantity) }} vị trí</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-admin-layout>
