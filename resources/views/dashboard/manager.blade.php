@php
    $displayName = Auth::user()->displayName();
    $managerName = $employeeProfile?->full_name ?? Auth::user()->name;
    $departmentName = $department?->department_name ?? 'Chưa gắn phòng ban';
    $statusClasses = [
        'active' => 'bg-blue-50 text-blue-700 border-blue-100',
        'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
        'resigned' => 'bg-rose-50 text-rose-700 border-rose-100',
        'on_leave' => 'bg-sky-50 text-sky-700 border-sky-100',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-blue-50 text-blue-700 border-blue-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
        'open' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $employeeStatusLabels = \App\Models\Employee::STATUS_LABELS;
    $employeeStatusClasses = \App\Models\Employee::STATUS_BADGE_CLASSES;
    $leaveTypeLabels = \App\Models\LeaveRequest::LEAVE_TYPE_LABELS;
    $leaveStatusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
    $jobStatusLabels = [
        'open' => 'Đang tuyển',
        'closed' => 'Đã đóng',
    ];
    $kpiCards = [
        ['label' => 'KPI chờ bắt đầu', 'value' => (int) ($kpiStatus->pending ?? 0), 'tone' => 'from-amber-400 to-orange-500'],
        ['label' => 'KPI đang chạy', 'value' => (int) ($kpiStatus->in_progress ?? 0),     'tone' => 'from-teal-400 to-emerald-500'],
        ['label' => 'KPI hoàn thành', 'value' => (int) ($kpiStatus->completed ?? 0), 'tone' => 'from-emerald-400 to-teal-500'],
        ['label' => 'KPI không hoàn thành', 'value' => (int) ($kpiStatus->not_completed ?? 0), 'tone' => 'from-rose-400 to-red-500'],
    ];
@endphp

<x-manager-layout
    title="Bảng điều khiển quản lý"
    subtitle="Theo dõi đội ngũ, phê duyệt yêu cầu và nhịp độ vận hành của phòng ban."
>
    <div class="manager-page w-full">
    <section id="overview" class="manager-welcome">
        <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-teal-100">Xin chào,</p>
                <h2 class="text-2xl font-bold tracking-tight">{{ $displayName }}</h2>
                <p class="mt-1 text-sm text-teal-100/90">
                    {{ $departmentName }} · {{ number_format($pendingLeaves) }} đơn chờ duyệt · {{ number_format($teamMembers->count()) }} nhân viên
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="#approvals" class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-teal-700 shadow-sm hover:bg-teal-50">Phê duyệt</a>
                <a href="#team" class="inline-flex items-center rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/25">Đội ngũ</a>
            </div>
        </div>
    </section>

    @if (! $employeeProfile)
        <div class="manager-card mb-8 border border-amber-100 bg-amber-50/90 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-amber-800">Tài khoản manager chưa liên kết hồ sơ nhân sự</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Dashboard vẫn hiển thị được giao diện, nhưng để lấy đúng phòng ban và dữ liệu đội nhóm bạn cần map tài khoản này với bảng `employees`.
                    </p>
                </div>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                    Cập nhật hồ sơ
                </a>
            </div>
        </div>
    @endif

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
        @php
            $managerStats = [
                ['value' => number_format($teamCount), 'label' => 'Nhân sự phòng ban', 'tone' => 'from-teal-500 to-emerald-500'],
                ['value' => number_format($activeCount), 'label' => 'Đang hoạt động', 'tone' => 'from-emerald-500 to-cyan-500'],
                ['value' => number_format($pendingLeaves).' / '.number_format($totalLeaves), 'label' => 'Đơn chờ / Tổng đơn', 'tone' => 'from-amber-500 to-orange-500'],
                ['value' => number_format($kpiInProgress), 'label' => 'KPI đang theo dõi', 'tone' => 'from-sky-500 to-blue-500'],
                ['value' => number_format($todayCheckIns), 'label' => 'Check-in hôm nay', 'tone' => 'from-violet-500 to-fuchsia-500'],
            ];
        @endphp
        @foreach ($managerStats as $stat)
            <div class="manager-stat-card">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $stat['tone'] }} text-xs font-bold text-white shadow-sm">
                    {{ mb_substr($stat['label'], 0, 1) }}
                </div>
                <p class="mt-3 text-2xl font-bold tabular-nums tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="team" class="manager-panel">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-teal-600">Đội ngũ phòng ban</p>
                            <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">{{ $departmentName }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $department?->description ?: 'Khu vực này giúp manager theo dõi thành viên mới nhất và trạng thái làm việc của cả đội.' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-teal-100 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700">
                                {{ number_format($teamCount) }} thành viên
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$department?->status ?? 'inactive'] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                {{ $department?->status === 'active' ? 'Phòng ban hoạt động' : 'Chưa sẵn sàng' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 border-b border-slate-100 px-6 py-5 sm:grid-cols-3 sm:px-7">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Manager</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ $managerName }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->position_name ?? 'Quản lý phòng ban' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Thông báo chưa đọc</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ number_format($unreadNotifications) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Cập nhật nội bộ và nhắc việc</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tin tuyển mở</p>
                        <p class="mt-3 text-lg font-bold text-slate-800">{{ number_format($openJobs) }}</p>
                        <p class="mt-1 text-sm text-slate-500">Nhu cầu tuyển dụng hiện tại</p>
                    </div>
                </div>

                <div class="px-6 py-5 sm:px-7">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h4 class="text-base font-bold text-slate-800">Thành viên cập nhật gần đây</h4>
                            <p class="text-sm text-slate-500">Danh sách nhân sự mới hoặc vừa thay đổi trạng thái trong đội.</p>
                        </div>
                    </div>

                    @if ($teamMembers->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Chưa có thành viên nào được gắn vào phòng ban này.</p>
                            <p class="mt-1 text-sm text-slate-500">Khi dữ liệu nhân sự được cập nhật, danh sách đội ngũ sẽ xuất hiện ở đây.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($teamMembers as $member)
                                <div class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white px-4 py-4 shadow-sm shadow-slate-100 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-100 to-emerald-100 text-sm font-bold text-teal-700">
                                            {{ strtoupper(mb_substr($member->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $member->full_name }}</p>
                                            <p class="text-sm text-slate-500">
                                                {{ $member->position_name ?? 'Chưa có chức vụ' }} · {{ $member->employee_code }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $employeeStatusClasses[$member->status] ?? ($statusClasses[$member->status] ?? 'border-slate-200 bg-slate-100 text-slate-600') }}">
                                            {{ $employeeStatusLabels[$member->status] ?? ucfirst($member->status) }}
                                        </span>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            {{ \Illuminate\Support\Carbon::parse($member->hire_date)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section id="approvals" class="manager-panel">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-600">Hàng đợi phê duyệt</p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Các yêu cầu cần xử lý</h3>
                    <p class="mt-1 text-sm text-slate-500">Ưu tiên duyệt nhanh các đơn nghỉ để đội ngũ không bị gián đoạn lịch làm việc.</p>
                </div>

                <div class="px-6 py-5 sm:px-7">
                    @if ($approvalQueue->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Hiện không có đơn nghỉ nào cần manager xử lý.</p>
                            <p class="mt-1 text-sm text-slate-500">Khi phát sinh yêu cầu mới, hàng đợi phê duyệt sẽ hiển thị tại đây.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($approvalQueue as $request)
                                <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $request->full_name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $leaveTypeLabels[$request->leave_type] ?? ucfirst($request->leave_type) }}
                                                · {{ \Illuminate\Support\Carbon::parse($request->start_date)->format('d/m/Y') }}
                                                đến {{ \Illuminate\Support\Carbon::parse($request->end_date)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$request->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                                {{ $leaveStatusLabels[$request->status] ?? ucfirst($request->status) }}
                                            </span>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm shadow-slate-100">
                                                {{ \Illuminate\Support\Carbon::parse($request->created_at)->format('d/m H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section id="kpi" class="manager-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-teal-600">Nhịp KPI</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-800">Sức khỏe mục tiêu</h3>
                        <p class="mt-1 text-sm text-slate-500">Tỷ lệ hoàn thành giúp bạn nhìn nhanh nhịp độ đội nhóm.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Trung bình</p>
                        <p class="text-lg font-bold text-slate-800">{{ round($kpiStatus->average_progress ?? 0) }}%</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ($kpiCards as $card)
                        @php
                            $cardWidth = min(100, max(12, $teamCount > 0 ? round(($card['value'] / max($teamCount, 1)) * 100) : 12));
                        @endphp
                        <div class="rounded-3xl border border-slate-100 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-700">{{ $card['label'] }}</p>
                                <span class="text-lg font-bold text-slate-800">{{ number_format($card['value']) }}</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100">
                                <div
                                    class="h-2 rounded-full bg-gradient-to-r {{ $card['tone'] }}"
                                    @style(['width: ' . $cardWidth . '%'])
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="manager-card p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-teal-600">Hồ sơ quản lý</p>
                <h3 class="mt-2 text-xl font-bold text-slate-800">Tóm tắt tài khoản</h3>

                <div class="mt-6 space-y-4">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Họ tên</p>
                        <p class="mt-2 text-base font-bold text-slate-800">{{ $managerName }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Phòng ban</p>
                        <p class="mt-2 text-base font-bold text-slate-800">{{ $departmentName }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->position_name ?? 'Manager' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Liên hệ</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">{{ $employeeProfile?->email ?? Auth::user()->email }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $employeeProfile?->phone ?? 'Chưa cập nhật số điện thoại' }}</p>
                    </div>
                </div>
            </section>

            <section id="recruitment" class="manager-card p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Tuyển dụng</p>
                <h3 class="mt-2 text-xl font-bold text-slate-800">Tin tuyển của phòng ban</h3>
                <p class="mt-1 text-sm text-slate-500">Theo dõi những vị trí đang mở và nhu cầu bổ sung nhân sự.</p>

                @if ($recruitmentPosts->isEmpty())
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                        <p class="text-sm font-semibold text-slate-700">Chưa có tin tuyển nào trong phòng ban.</p>
                        <p class="mt-1 text-sm text-slate-500">Khi có nhu cầu tuyển thêm người, danh sách sẽ xuất hiện tại đây.</p>
                    </div>
                @else
                    <div class="mt-6 space-y-3">
                        @foreach ($recruitmentPosts as $post)
                            <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $post->title }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Nhu cầu: {{ number_format($post->quantity) }} người</p>
                                    </div>
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses[$post->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                        {{ $jobStatusLabels[$post->status] ?? ucfirst($post->status) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-slate-400">Cập nhật {{ \Illuminate\Support\Carbon::parse($post->created_at)->format('d/m/Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
    </div>
</x-manager-layout>
