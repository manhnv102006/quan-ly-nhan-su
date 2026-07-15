@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.payrolls.partials.sub-nav', ['active' => 'history'])

<x-accountant-layout title="Lịch sử thay đổi lương" subtitle="Hợp đồng · Bảng lương · Điều chỉnh">
    <div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Lịch sử thay đổi lương</h2>
            <p class="text-sm text-slate-500">Theo dõi mức lương qua hợp đồng, kỳ lương và các lần điều chỉnh</p>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[240px] flex-1">
                <label class="accountant-label">Chọn nhân viên</label>
                <select name="employee_id" class="accountant-field" onchange="this.form.submit()">
                    <option value="">-- Chọn nhân viên --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($selectedEmployee)
            <div class="accountant-card p-5">
                <h3 class="font-bold text-slate-800">{{ $selectedEmployee->full_name }}</h3>
                <p class="text-sm text-slate-500">
                    {{ $selectedEmployee->employee_code }}
                    · {{ $selectedEmployee->department?->department_name ?? '—' }}
                    · {{ $selectedEmployee->position?->position_name ?? '—' }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="accountant-card overflow-hidden">
                    <div class="border-b border-amber-100/80 px-5 py-4">
                        <h3 class="text-sm font-bold text-slate-800">Thay đổi từ hợp đồng</h3>
                    </div>
                    <div class="max-h-[420px] space-y-3 overflow-y-auto p-5">
                        @forelse($contractHistories as $history)
                            <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 text-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="accountant-badge {{ $history->action_badge_class }}">{{ $history->action_label }}</span>
                                    <span class="text-xs text-slate-500">{{ $history->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="mt-2 font-medium text-slate-800">{{ $history->summary }}</p>
                                @if(!empty($history->changes['salary']))
                                    @php $sal = $history->changes['salary']; @endphp
                                    <p class="mt-1 text-amber-800">
                                        Lương:
                                        @if(isset($sal['old'])) <span class="text-slate-500 line-through">{{ $formatMoney($sal['old']) }}</span> → @endif
                                        <strong>{{ $formatMoney($sal['new'] ?? $sal['old'] ?? 0) }}</strong>
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-slate-500">Bởi: {{ $history->performer?->name ?? 'Hệ thống' }}</p>
                            </div>
                        @empty
                            <p class="text-center text-sm text-slate-500 py-6">Chưa có lịch sử thay đổi lương từ hợp đồng.</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="accountant-card overflow-hidden">
                        <div class="border-b border-amber-100/80 px-5 py-4">
                            <h3 class="text-sm font-bold text-slate-800">Bảng lương theo kỳ</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                                        <th class="px-4 py-3">Kỳ</th>
                                        <th class="px-4 py-3 text-right">Thực lĩnh</th>
                                        <th class="px-4 py-3">TT</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($payrollRecords as $record)
                                        <tr>
                                            <td class="px-4 py-3">{{ $record->payrollPeriod?->name }}</td>
                                            <td class="px-4 py-3 text-right font-bold text-amber-800">{{ $formatMoney($record->total_salary) }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $record->statusLabel() }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Chưa có bảng lương.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="accountant-card overflow-hidden">
                        <div class="border-b border-amber-100/80 px-5 py-4">
                            <h3 class="text-sm font-bold text-slate-800">Điều chỉnh thưởng/khấu trừ</h3>
                        </div>
                        <div class="max-h-[200px] space-y-2 overflow-y-auto p-5 text-sm">
                            @forelse($adjustmentLogs as $log)
                                <div class="rounded-lg border border-orange-100 bg-orange-50/50 px-3 py-2">
                                    <p class="font-medium text-slate-800">{{ $log->description }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->created_at?->format('d/m/Y H:i') }} · {{ $log->causer?->name ?? '—' }}</p>
                                </div>
                            @empty
                                <p class="text-center text-slate-500 py-4">Chưa có điều chỉnh nào.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="accountant-card p-10 text-center text-slate-500">Chọn nhân viên để xem lịch sử thay đổi lương.</div>
        @endif
    </div>
</x-accountant-layout>
