@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $totalActive = $departments->sum('active_contracts_count');
    $totalContracts = $departments->sum('contracts_count');
    $totalEmployees = $departments->sum('employees_count');
@endphp

<x-accountant-layout title="Hợp đồng" subtitle="Phòng ban → Nhân viên → Hợp đồng">
<div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Hợp đồng lao động</h2>
                <p class="text-sm text-slate-500">Xem mức lương, phụ cấp và trạng thái hợp đồng theo phòng ban.</p>
            </div>
            <a href="{{ route('accountant.contracts.salary-overview') }}" class="accountant-btn-secondary">Bảng lương & phụ cấp</a>
        </div>

        @if(($expiringCount ?? 0) > 0)
            <div class="rounded-2xl border border-rose-200 bg-gradient-to-r from-rose-50 to-orange-50 p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-rose-800">Cảnh báo hợp đồng sắp hết hạn</p>
                        <p class="mt-1 text-sm text-rose-700">
                            Có <span class="font-bold">{{ $expiringCount }}</span> hợp đồng hiệu lực sẽ hết hạn trong 30 ngày tới.
                        </p>
                    </div>
                    <a href="{{ route('accountant.contracts.expiring') }}" class="accountant-btn-primary !bg-rose-600 hover:!bg-rose-700">
                        Xem danh sách cảnh báo
                    </a>
                </div>

                @if($expiringSoon->isNotEmpty())
                    <div class="mt-4 overflow-x-auto rounded-xl border border-rose-100 bg-white/80">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase text-slate-500">
                                    <th class="px-4 py-3">Mã HĐ</th>
                                    <th class="px-4 py-3">Nhân viên</th>
                                    <th class="px-4 py-3">Hết hạn</th>
                                    <th class="px-4 py-3 text-right">Lương</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rose-50">
                                @foreach($expiringSoon as $contract)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-rose-800">{{ $contract->contract_code }}</td>
                                        <td class="px-4 py-3">
                                            {{ $contract->employee->full_name ?? '—' }}
                                            <span class="block text-xs text-slate-500">{{ $contract->employee->department->department_name ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-rose-700">{{ optional($contract->end_date)->format('d/m/Y') }}</span>
                                            <span class="block text-xs text-rose-600">
                                                Còn {{ (int) now()->startOfDay()->diffInDays($contract->end_date, false) }} ngày
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ $formatMoney($contract->salary) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('accountant.contracts.show', $contract) }}" class="text-xs font-bold text-amber-700 hover:underline">Chi tiết</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count()])
            @include('accountant.partials.stat-card', ['label' => 'HĐ hiệu lực', 'value' => $totalActive, 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng hợp đồng', 'value' => $totalContracts, 'tone' => 'text-rose-600'])
            @include('accountant.partials.stat-card', ['label' => 'Sắp hết hạn', 'value' => $expiringCount ?? 0, 'tone' => ($expiringCount ?? 0) > 0 ? 'text-rose-600' : 'text-slate-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban · {{ $totalEmployees }} nhân viên</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.contracts.index', ['department_id' => $department->id]) }}"
                       class="group rounded-2xl border border-amber-100/80 bg-gradient-to-br from-amber-50/40 to-orange-50/30 p-5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-amber-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-amber-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-emerald-600">{{ $department->active_contracts_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Hiệu lực</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold {{ ($department->expiring_count ?? 0) > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                    {{ $department->expiring_count ?? 0 }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-500">Sắp HH</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
