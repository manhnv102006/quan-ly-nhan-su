@php
    $navigation = \App\Support\EmployeeNavigation::items();

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

<x-staff-layout title="Đơn tăng ca" subtitle="Xem và gửi yêu cầu tăng ca." role="employee" :navigation="$navigation">

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