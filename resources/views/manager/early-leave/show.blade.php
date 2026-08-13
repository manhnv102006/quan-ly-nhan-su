<x-manager-layout title="Chi tiết đơn về sớm" subtitle="{{ $earlyLeaveRequest->employee?->full_name }}">
    <div class="manager-page space-y-6">
        <a href="{{ route('manager.early-leave.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">← Danh sách đơn về sớm</a>

        <div class="manager-card p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                <div><p class="text-xs text-slate-400">Nhân viên</p><p class="font-semibold">{{ $earlyLeaveRequest->employee?->full_name }}</p></div>
                <div><p class="text-xs text-slate-400">Phòng ban</p><p class="font-semibold">{{ $earlyLeaveRequest->employee?->department?->department_name ?? '—' }}</p></div>
                <div><p class="text-xs text-slate-400">Ngày</p><p class="font-semibold">{{ $earlyLeaveRequest->request_date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-slate-400">Giờ về</p><p class="font-semibold text-violet-600">{{ \Carbon\Carbon::parse($earlyLeaveRequest->leave_time)->format('H:i') }}</p></div>
                <div><p class="text-xs text-slate-400">Trạng thái</p><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $earlyLeaveRequest->statusBadgeClass() }}">{{ $earlyLeaveRequest->statusLabel() }}</span></div>
                <div class="md:col-span-2"><p class="text-xs text-slate-400">Lý do</p><p class="text-slate-700">{{ $earlyLeaveRequest->reason }}</p></div>
            </div>

            @if ($earlyLeaveRequest->isPending())
                <div class="mt-6 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('manager.early-leave.approve', $earlyLeaveRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Duyệt</button>
                    </form>
                </div>
            @endif
        </div>

        @include('request-approvals.partials.processing-history', [
            'requestModel' => $earlyLeaveRequest,
            'title' => 'Lịch sử xử lý',
        ])
    </div>
</x-manager-layout>
