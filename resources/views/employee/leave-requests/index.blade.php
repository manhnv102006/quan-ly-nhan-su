@php
    $user = Auth::user();
    $isAdmin = $user->role->name === 'admin';
    $isManager = $user->role->name === 'manager';

    $navigation = [];
    if ($isManager) {
        $navigation = \App\Support\ManagerNavigation::items();
    } else {
        $navigation = \App\Support\EmployeeNavigation::items();
    }

    $layout = match (true) {
        $isAdmin => 'admin-layout',
        $isManager => 'manager-layout',
        default => 'employee-layout',
    };
    $layoutParams = $isAdmin
        ? ['title' => 'Đơn nghỉ phép của tôi']
        : [
            'title' => 'Đơn nghỉ phép của tôi',
            'subtitle' => 'Xem danh sách và gửi yêu cầu nghỉ phép.',
        ];

    $leaveTypes = [
        'annual' => ['label' => 'Nghỉ phép năm', 'class' => 'bg-sky-50 text-sky-700 border-sky-100'],
        'sick' => ['label' => 'Nghỉ ốm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'unpaid' => ['label' => 'Nghỉ không lương', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
    ];

    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        @if (session('success'))
            <div id="success-toast" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Lịch sử nghỉ phép của bạn</h2>
                <p class="text-xs text-slate-500 mt-1">
                    @if ($isManager)
                        Đơn của quản lý do Admin phê duyệt.
                    @else
                        Danh sách đơn đã gửi và trạng thái xử lý.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($isManager)
                    <a href="{{ route('manager.leave-requests.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                        ← Duyệt đơn nhân viên
                    </a>
                @endif
                <a href="{{ route('employee.leave-requests.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 text-white font-semibold text-xs shadow-md shadow-sky-500/20 hover:bg-sky-700 transition">
                    <span>➕</span> Tạo đơn nghỉ phép
                </a>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Loại nghỉ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian nghỉ</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Số ngày</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Người duyệt</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leaveRequests as $request)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-semibold {{ $leaveTypes[$request->leave_type]['class'] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $leaveTypes[$request->leave_type]['label'] ?? $request->leave_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700 text-xs font-medium">
                                    {{ $request->start_date->format('d/m/Y') }} → {{ $request->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-center text-slate-800 font-bold text-xs">
                                    {{ $request->total_days }} ngày
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs max-w-[200px] truncate" title="{{ $request->reason }}">
                                    {{ $request->reason }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClasses[$request->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabels[$request->status] ?? $request->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">
                                    @if ($request->status === 'approved' && $request->approver)
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $request->approver->name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">lúc {{ $request->approved_at?->format('H:i d/m/Y') }}</span>
                                        </div>
                                    @elseif ($request->status === 'rejected' && $request->rejecter)
                                        <div>
                                            <span class="font-bold text-slate-700">{{ $request->rejecter->name }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">lúc {{ $request->rejected_at?->format('H:i d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('employee.leave-requests.show', $request) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 border border-sky-100 text-xs font-semibold hover:bg-sky-100 transition">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400 text-sm">
                                    Bạn chưa gửi đơn xin nghỉ phép nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaveRequests->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        const toast = document.getElementById('success-toast');
        if (toast) {
            setTimeout(function () {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-dynamic-component>
