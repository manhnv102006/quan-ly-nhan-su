@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Trừ tạm ứng vào lương" subtitle="Khấu trừ vào bảng lương kỳ calculated">
    @include('accountant.advances.partials.sub-nav', ['active' => 'deduct'])
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Trừ tạm ứng vào kỳ lương</h2>
                <p class="text-sm text-slate-500">Chỉ áp dụng khi bảng lương ở trạng thái <strong>calculated</strong> (chưa duyệt)</p>
            </div>
            @if($period)
                <form method="POST" action="{{ route('accountant.advances.apply-all', $period) }}" onsubmit="return confirm('Trừ tự động toàn bộ tạm ứng còn dư vào kỳ này?')">
                    @csrf
                    <button type="submit" class="accountant-btn-primary">Trừ tất cả tự động</button>
                </form>
            @endif
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[220px] flex-1">
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field" onchange="this.form.submit()">
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" @selected($period?->id === $p->id)>{{ $p->name }} ({{ $p->status }})</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($period)
            <div class="accountant-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-sm">
                        <thead>
                            <tr class="bg-cyan-50/80 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-4 py-3">Mã TU</th>
                                <th class="px-4 py-3">Nhân viên</th>
                                <th class="px-4 py-3 text-right">Còn lại</th>
                                <th class="px-4 py-3">Bảng lương kỳ</th>
                                <th class="px-4 py-3 text-right">Thực lĩnh hiện tại</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rows as $row)
                                @php $adv = $row['advance']; $pay = $row['payroll']; @endphp
                                <tr class="hover:bg-cyan-50/30">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $adv->advance_code }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $adv->employee?->full_name }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-rose-700">{{ $formatMoney($row['remaining']) }}</td>
                                    <td class="px-4 py-3">
                                        @if($pay)
                                            <span class="text-emerald-700">Có · {{ $pay->statusLabel() }}</span>
                                        @else
                                            <span class="text-amber-600">Chưa tính lương / chưa calculated</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ $pay ? $formatMoney($pay->total_salary) : '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($pay && $pay->status === 'calculated')
                                            <form method="POST" action="{{ route('accountant.advances.apply', $adv) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="payroll_id" value="{{ $pay->id }}">
                                                <button type="submit" class="accountant-btn-secondary !py-1 !text-xs">Trừ {{ $formatMoney(min($row['remaining'], (float)$pay->total_salary)) }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Không có tạm ứng cần trừ hoặc kỳ chưa sẵn sàng.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-accountant-layout>
