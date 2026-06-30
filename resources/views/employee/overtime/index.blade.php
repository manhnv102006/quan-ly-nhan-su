@php
    $user = Auth::user();

    $navigation = [
        ['label' => 'Dashboard', 'href' => route('employee.dashboard'), 'route' => 'employee.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z', 'note' => 'Không gian cá nhân'],
        ['label' => 'Chấm công', 'href' => route('attendance.index'), 'route' => 'attendance.index', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Check-in / Check-out'],
        ['label' => 'Tăng ca', 'href' => route('employee.overtime-requests'), 'route' => 'employee.overtime-requests*', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Đơn xin tăng ca'],
        ['label' => 'Nghỉ phép', 'href' => route('employee.leave-requests'), 'route' => 'employee.leave-requests*', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z', 'note' => 'Đơn xin nghỉ phép'],
        ['label' => 'Bảng lương', 'href' => route('employee.payrolls.index'), 'route' => 'employee.payrolls*', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'note' => 'Tổng thu nhập'],
        ['label' => 'Hồ sơ', 'href' => route('profile.edit'), 'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'note' => 'Thông tin cá nhân'],
    ];

    $statusLabels = [
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    $statusClasses = [
        'pending'  => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
@endphp

<x-staff-layout title="Đơn tăng ca" role="employee" :navigation="$navigation">

    <div class="space-y-6">

        {{-- Flash --}}
        @if (session('success'))
            <div id="toast-success" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Đơn tăng ca của tôi</h1>
                <p class="text-xs text-slate-500 mt-1">Danh sách đơn đã gửi và trạng thái xử lý.</p>
            </div>
        </div>

        {{-- Bảng --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Ngày tăng ca</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Số giờ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($overtimeRequests as $ot)
                            @php
                                $start = \Carbon\Carbon::parse($ot->start_time);
                                $end   = \Carbon\Carbon::parse($ot->end_time);
                                $hours = round($start->diffInMinutes($end) / 60, 1);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ $ot->overtime_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $start->format('H:i') }} → {{ $end->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">
                                    {{ $hours }}h
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-[220px] truncate" title="{{ $ot->reason }}">
                                    {{ $ot->reason }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClasses[$ot->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $statusLabels[$ot->status] ?? $ot->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-14 text-slate-400 text-sm">
                                    Bạn chưa có đơn tăng ca nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($overtimeRequests->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $overtimeRequests->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        const toast = document.getElementById('toast-success');
        if (toast) {
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>

</x-staff-layout>