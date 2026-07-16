@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Hợp đồng sắp hết hạn" subtitle="Cảnh báo hết hạn hợp đồng lao động">
    @include('accountant.contracts.partials.sub-nav', ['active' => 'expiring'])

    <div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Cảnh báo hợp đồng sắp hết hạn</h2>
            <p class="text-sm text-slate-500">Theo dõi hợp đồng hiệu lực sắp đến ngày kết thúc để chuẩn bị gia hạn hoặc điều chỉnh lương.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach([
                ['label' => 'Trong 7 ngày', 'value' => $stats['within_7'], 'days' => 7, 'tone' => 'text-rose-700'],
                ['label' => 'Trong 15 ngày', 'value' => $stats['within_15'], 'days' => 15, 'tone' => 'text-orange-600'],
                ['label' => 'Trong 30 ngày', 'value' => $stats['within_30'], 'days' => 30, 'tone' => 'text-amber-600'],
                ['label' => 'Trong 60 ngày', 'value' => $stats['within_60'], 'days' => 60, 'tone' => 'text-slate-600'],
            ] as $stat)
                <a href="{{ route('accountant.contracts.expiring', ['days' => $stat['days']]) }}"
                   class="accountant-card block p-5 transition hover:-translate-y-0.5 hover:shadow-md {{ $days === $stat['days'] ? 'ring-2 ring-rose-300' : '' }}">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-extrabold {{ $stat['tone'] }}">{{ $stat['value'] }}</p>
                </a>
            @endforeach
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[160px]">
                <label class="accountant-label">Khung thời gian</label>
                <select name="days" class="accountant-field" onchange="this.form.submit()">
                    @foreach([7, 15, 30, 60, 90] as $option)
                        <option value="{{ $option }}" @selected($days === $option)>{{ $option }} ngày tới</option>
                    @endforeach
                </select>
            </div>
            <p class="text-sm text-slate-500">
                Đang hiển thị <span class="font-bold text-rose-700">{{ $contracts->count() }}</span> hợp đồng hết hạn trong {{ $days }} ngày.
            </p>
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead>
                        <tr class="bg-rose-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Mức độ</th>
                            <th class="px-4 py-3">Mã HĐ</th>
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Phòng ban</th>
                            <th class="px-4 py-3">Ngày hết hạn</th>
                            <th class="px-4 py-3 text-right">Lương</th>
                            <th class="px-4 py-3 text-right">Phụ cấp</th>
                            <th class="px-4 py-3 text-right">Tổng TN</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $row)
                            @php
                                $contract = $row['contract'];
                                $daysLeft = $row['days_left'];
                                $urgency = $daysLeft <= 7 ? 'Khẩn' : ($daysLeft <= 15 ? 'Cảnh báo' : 'Theo dõi');
                                $urgencyClass = $daysLeft <= 7 ? 'bg-rose-100 text-rose-700' : ($daysLeft <= 15 ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700');
                            @endphp
                            <tr class="hover:bg-rose-50/30">
                                <td class="px-4 py-3">
                                    <span class="accountant-badge {{ $urgencyClass }}">{{ $urgency }}</span>
                                    <span class="mt-1 block text-xs font-bold text-rose-600">Còn {{ $daysLeft }} ngày</span>
                                </td>
                                <td class="px-4 py-3 font-bold text-rose-800">{{ $contract->contract_code }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold">{{ $contract->employee->full_name ?? '—' }}</span>
                                    <span class="block text-xs text-slate-500">{{ $contract->employee->employee_code ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $contract->employee->department->department_name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-rose-700">{{ optional($contract->end_date)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">{{ $formatMoney($contract->salary) }}</td>
                                <td class="px-4 py-3 text-right text-amber-700">{{ $formatMoney($row['allowance']) }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ $formatMoney($row['total_income']) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('accountant.contracts.show', $contract) }}" class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center text-slate-500">
                                    Không có hợp đồng nào hết hạn trong {{ $days }} ngày tới.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-slate-500">
            Kế toán chỉ xem thông tin hợp đồng. Gia hạn hoặc ký mới do bộ phận nhân sự / quản trị thực hiện.
        </p>
    </div>
</x-accountant-layout>
