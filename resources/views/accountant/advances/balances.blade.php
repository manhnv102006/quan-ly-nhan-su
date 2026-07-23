@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

<x-accountant-layout title="Số dư tạm ứng" subtitle="Theo dõi dư nợ tạm ứng theo nhân viên">
<div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Số dư tạm ứng</h2>
            <p class="text-sm text-slate-500">Tổng hợp ứng lương đã duyệt và phần chưa trừ vào lương</p>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Tổng dư cần trừ', 'value' => $formatMoney($stats['total_outstanding']), 'tone' => 'text-rose-600'])
            @include('accountant.partials.stat-card', ['label' => 'NV có dư', 'value' => $balances->where('remaining', '>', 0)->count()])
            @include('accountant.partials.stat-card', ['label' => 'Chờ duyệt', 'value' => $stats['pending'], 'tone' => 'text-amber-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="bg-cyan-50/80 text-left text-xs font-bold uppercase text-slate-500">
                        <th class="px-5 py-3">Nhân viên</th>
                        <th class="px-5 py-3 text-center">Số khoản</th>
                        <th class="px-5 py-3 text-right">Tổng ứng</th>
                        <th class="px-5 py-3 text-right">Đã trừ</th>
                        <th class="px-5 py-3 text-right">Còn lại</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($balances as $row)
                        <tr class="hover:bg-cyan-50/30">
                            <td class="px-5 py-3">
                                <p class="font-semibold">{{ $row['employee']?->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $row['employee']?->employee_code }} · {{ $row['employee']?->department?->department_name }}</p>
                            </td>
                            <td class="px-5 py-3 text-center">{{ $row['active_count'] }}/{{ $row['count'] }}</td>
                            <td class="px-5 py-3 text-right">{{ $formatMoney($row['total_advanced']) }}</td>
                            <td class="px-5 py-3 text-right text-emerald-700">{{ $formatMoney($row['total_settled']) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-rose-700">{{ $formatMoney($row['remaining']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Chưa có số dư tạm ứng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-accountant-layout>
