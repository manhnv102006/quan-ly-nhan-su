@php
    $navigation = \App\Support\EmployeeNavigation::items();

    $statusLabels = \App\Models\OvertimeRequest::STATUS_LABELS;
    $statusClasses = \App\Models\OvertimeRequest::STATUS_TAILWIND_CLASSES;
@endphp

<x-staff-layout title="Đơn tăng ca" subtitle="Xem và gửi yêu cầu tăng ca." role="employee" :navigation="$navigation">

    <div class="space-y-6">

        @if (session('success'))
            <div id="toast-success" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Đơn tăng ca của tôi</h1>
                <p class="text-xs text-slate-500 mt-1">Gửi đơn → chờ duyệt → check-out → hệ thống ghi nhận giờ OT → tính vào bảng lương.</p>
            </div>
            <a href="{{ route('employee.overtime-requests.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold shadow-md shadow-amber-500/20 hover:bg-amber-700 transition">
                + Tạo đơn tăng ca
            </a>
        </div>

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
                                $end = \Carbon\Carbon::parse($ot->end_time);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ $ot->work_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $start->format('H:i') }} → {{ $end->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">
                                    {{ number_format((float) $ot->total_hours, 1) }}h
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
                                    <a href="{{ route('employee.overtime-requests.create') }}" class="text-amber-600 font-semibold hover:underline">Tạo đơn ngay</a>
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

</x-staff-layout>
