@php
    $leaveTypes = \App\Models\LeaveRequest::LEAVE_TYPE_LABELS;
@endphp

<x-leader-layout title="Duyệt nghỉ phép nhóm" subtitle="Duyệt bước 1 · Chuyển Manager">
    <div class="leader-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Duyệt nghỉ phép nhóm</h2>
            <p class="text-sm text-slate-500">Duyệt bước 1 cho thành viên nhóm, sau đó chuyển Quản lý phê duyệt bước 2.</p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Chờ bạn duyệt', 'value' => $stats['awaiting_leader'], 'tone' => 'text-amber-600'])
            @include('leader.partials.stat-card', ['label' => 'Chờ Manager', 'value' => $stats['awaiting_manager'], 'tone' => 'text-sky-600'])
            @include('leader.partials.stat-card', ['label' => 'Đã duyệt', 'value' => $stats['approved'], 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Từ chối', 'value' => $stats['rejected'], 'tone' => 'text-rose-600'])
        </div>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Loại nghỉ</th>
                            <th class="px-4 py-3">Thời gian</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($leaveRequests as $leaveRequest)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $leaveRequest->employee?->full_name }}</td>
                                <td class="px-4 py-3">{{ $leaveTypes[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</td>
                                <td class="px-4 py-3">{{ $leaveRequest->start_date?->format('d/m/Y') }} - {{ $leaveRequest->end_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="leader-badge {{ $leaveRequest->isAwaitingLeaderApproval() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $leaveRequest->workflowStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('leader.leave-requests.show', $leaveRequest) }}" class="leader-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center text-slate-500">Chưa có đơn nghỉ phép.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaveRequests->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $leaveRequests->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
