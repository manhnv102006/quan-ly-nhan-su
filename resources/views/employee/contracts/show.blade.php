@php
    $user = Auth::user();
    $layout = $user->role->name === 'manager' ? 'manager-layout' : 'employee-layout';
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag(['title' => 'Chi tiết hợp đồng'])">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $contract->contract_code }}</h2>
                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                </div>
                <p class="text-sm text-slate-500">{{ $contract->contractType->contract_name ?? '—' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('employee.contracts.index') }}" class="admin-btn-secondary">Danh sách</a>
                @if($contract->file_path)
                    <a href="{{ route('employee.contracts.download', $contract) }}" class="admin-btn-violet">Tải file HĐ</a>
                @endif
            </div>
        </div>

        @include('contracts.partials.detail', [
            'contract' => $contract,
            'allowanceBreakdown' => $allowanceBreakdown,
            'totalAllowance' => $totalAllowance,
            'showEmployee' => false,
        ])
    </div>
</x-dynamic-component>
