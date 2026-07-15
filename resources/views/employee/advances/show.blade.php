@php
    $user = Auth::user();
    $role = $user->role->name ?? 'employee';
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';

    $layout = match ($role) {
        'manager' => 'manager-layout',
        'leader' => 'leader-layout',
        'accountant' => 'accountant-layout',
        default => 'employee-layout',
    };

    $layoutParams = [
        'title' => 'Chi tiết ứng lương',
        'subtitle' => $advance->advance_code,
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-3xl space-y-6">
        <a href="{{ route('employee.advances.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            ← Quay lại danh sách
        </a>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $advance->advance_code }}</h2>
                    <p class="text-sm text-slate-500">Ngày gửi: {{ $advance->request_date?->format('d/m/Y') }}</p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase {{ $advance->statusBadgeClass() }}">
                    {{ $advance->statusLabel() }}
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Số tiền ứng</dt>
                    <dd class="mt-1 text-xl font-bold text-cyan-800">{{ $formatMoney($advance->amount) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Còn phải trừ</dt>
                    <dd class="mt-1 text-xl font-bold text-rose-700">{{ $formatMoney($advance->remainingBalance()) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Đã trừ vào lương</dt>
                    <dd class="mt-1 text-lg font-bold text-emerald-700">{{ $formatMoney($advance->amount_settled) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Người duyệt</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-800">
                        @if($advance->approver)
                            {{ $advance->approver->name }}
                            <span class="block text-xs font-normal text-slate-400">{{ $advance->approved_at?->format('d/m/Y H:i') }}</span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-5 space-y-3 text-sm">
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">Lý do</p>
                    <p class="mt-1 text-slate-700">{{ $advance->reason }}</p>
                </div>
                @if($advance->note)
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Ghi chú</p>
                        <p class="mt-1 text-slate-600">{{ $advance->note }}</p>
                    </div>
                @endif
                @if($advance->rejection_reason)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                        <p class="text-xs font-bold uppercase">Lý do từ chối</p>
                        <p class="mt-1">{{ $advance->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($advance->deductions->isNotEmpty())
            <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-800">Lịch sử trừ vào lương thực lĩnh</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                                <th class="px-5 py-3">Kỳ lương</th>
                                <th class="px-5 py-3 text-right">Số tiền trừ</th>
                                <th class="px-5 py-3">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($advance->deductions as $deduction)
                                <tr>
                                    <td class="px-5 py-3">{{ $deduction->payrollPeriod?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right font-bold text-rose-700">-{{ $formatMoney($deduction->amount) }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $deduction->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
