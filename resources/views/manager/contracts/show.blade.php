<x-manager-layout title="Chi tiết hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $contract->contract_code }}</h2>
                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                </div>
                <p class="text-sm text-slate-500">{{ $contract->employee->full_name ?? '—' }} · {{ $contract->contractType->contract_name ?? '—' }}</p>
            </div>
            <a href="{{ route('manager.contracts.index') }}" class="admin-btn-secondary">Quay lại danh sách</a>
        </div>

        <div class="rounded-xl border border-violet-100 bg-violet-50/40 px-4 py-3 text-sm text-violet-800">
            Bạn đang xem ở chế độ <strong>chỉ đọc</strong>. Liên hệ Admin để gia hạn hoặc chấm dứt hợp đồng.
        </div>

        @include('contracts.partials.detail', [
            'contract' => $contract,
            'allowanceBreakdown' => $allowanceBreakdown,
            'totalAllowance' => $totalAllowance,
            'history' => $history,
        ])
    </div>
</x-manager-layout>
