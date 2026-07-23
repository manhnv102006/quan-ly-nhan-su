@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Chi tiết tạm ứng" subtitle="{{ $advance->advance_code }}">
<div class="accountant-page">
        <nav class="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="{{ route('accountant.advances.index') }}" class="text-cyan-700 hover:underline">Phòng ban</a>
            @if($advance->employee?->department)
                <span>/</span>
                <a href="{{ route('accountant.advances.index', ['department_id' => $advance->employee->department->id]) }}" class="text-cyan-700 hover:underline">{{ $advance->employee->department->department_name }}</a>
            @endif
            @if($advance->employee)
                <span>/</span>
                <a href="{{ route('accountant.advances.index', ['employee_id' => $advance->employee_id]) }}" class="text-cyan-700 hover:underline">{{ $advance->employee->full_name }}</a>
            @endif
            <span>/</span>
            <span class="text-slate-700">{{ $advance->advance_code }}</span>
        </nav>

        <div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="accountant-card p-5 lg:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold">{{ $advance->advance_code }}</h2>
                        <p class="text-sm text-slate-500">{{ $advance->employee?->full_name }} · {{ $advance->request_date?->format('d/m/Y') }}</p>
                    </div>
                    <span class="accountant-badge {{ $advance->statusBadgeClass() }}">{{ $advance->statusLabel() }}</span>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-slate-500">Số tiền:</span> <strong class="text-cyan-800">{{ $formatMoney($advance->amount) }}</strong></div>
                    <div><span class="text-slate-500">Đã trừ:</span> <strong>{{ $formatMoney($advance->amount_settled) }}</strong></div>
                    <div><span class="text-slate-500">Còn lại:</span> <strong class="text-rose-700">{{ $formatMoney($advance->remainingBalance()) }}</strong></div>
                    <div><span class="text-slate-500">Lý do:</span> {{ $advance->reason }}</div>
                </div>
                @if($advance->rejection_reason)
                    <p class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-800">Từ chối: {{ $advance->rejection_reason }}</p>
                @endif
            </div>

            <div class="space-y-3">
                @if($advance->canBeApproved())
                    <form method="POST" action="{{ route('accountant.advances.approve', $advance) }}">
                        @csrf
                        <button class="accountant-btn-primary w-full">Duyệt tạm ứng</button>
                    </form>
                @endif
                @if($advance->canBeDeducted())
                    <a href="{{ route('accountant.advances.deduct') }}" class="accountant-btn-secondary block w-full text-center">Trừ vào kỳ lương →</a>
                @endif
            </div>
        </div>

        @if($advance->deductions->isNotEmpty())
            <div class="accountant-card mt-6 overflow-hidden">
                <div class="border-b px-5 py-3 font-bold text-slate-800">Lịch sử trừ lương</div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Kỳ lương</th>
                            <th class="px-4 py-3 text-right">Số tiền trừ</th>
                            <th class="px-4 py-3">Người thực hiện</th>
                            <th class="px-4 py-3">Ngày</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($advance->deductions as $d)
                            <tr>
                                <td class="px-4 py-3">{{ $d->payrollPeriod?->name }}</td>
                                <td class="px-4 py-3 text-right font-bold text-cyan-800">{{ $formatMoney($d->amount) }}</td>
                                <td class="px-4 py-3">{{ $d->deductor?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $d->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-accountant-layout>
