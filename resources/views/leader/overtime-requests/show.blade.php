<x-leader-layout title="Chi tiết đơn tăng ca" subtitle="{{ $overtimeRequest->employee?->full_name }}">
    <div class="leader-page">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('leader.overtime-requests.index') }}" class="text-xs font-semibold text-violet-700 hover:underline">← Danh sách</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Đơn tăng ca #{{ $overtimeRequest->id }}</h2>
            </div>
            @if($overtimeRequest->isAwaitingLeaderApproval())
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('leader.overtime-requests.approve', $overtimeRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="leader-btn-primary" onclick="return confirm('Duyệt bước 1?')">Duyệt bước 1</button>
                    </form>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-overtime')" class="leader-btn-secondary !border-rose-200 !text-rose-700">Từ chối</button>
                </div>
            @endif
        </div>

        <div class="leader-card p-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div><p class="text-xs text-slate-400">Nhân viên</p><p class="font-semibold">{{ $overtimeRequest->employee?->full_name }}</p></div>
                <div><p class="text-xs text-slate-400">Trạng thái</p><p class="font-semibold">{{ $overtimeRequest->workflowStatusLabel() }}</p></div>
                <div><p class="text-xs text-slate-400">Ngày tăng ca</p><p class="font-semibold">{{ $overtimeRequest->work_date?->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-slate-400">Khung giờ</p><p class="font-semibold">{{ $overtimeRequest->start_time }} - {{ $overtimeRequest->end_time }}</p></div>
                <div><p class="text-xs text-slate-400">Số giờ</p><p class="font-semibold">{{ $overtimeRequest->total_hours }}h</p></div>
                <div><p class="text-xs text-slate-400">Hệ số</p><p class="font-semibold">x{{ $overtimeRequest->rate_multiplier }}</p></div>
                <div class="md:col-span-2"><p class="text-xs text-slate-400">Lý do</p><p class="font-semibold">{{ $overtimeRequest->reason ?: '—' }}</p></div>
            </div>
        </div>

        @if($overtimeRequest->isAwaitingLeaderApproval())
            <x-modal name="reject-overtime" focusable>
                <form method="POST" action="{{ route('leader.overtime-requests.reject', $overtimeRequest) }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-bold text-slate-900">Từ chối đơn tăng ca</h3>
                    <div class="mt-4">
                        <label class="leader-label">Lý do từ chối</label>
                        <textarea name="reject_reason" rows="3" class="leader-field" required>{{ old('reject_reason') }}</textarea>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" x-on:click="$dispatch('close')" class="leader-btn-secondary">Hủy</button>
                        <button type="submit" class="leader-btn-primary !from-rose-500 !to-red-500">Xác nhận từ chối</button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</x-leader-layout>
