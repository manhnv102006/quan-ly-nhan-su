@php
    $user = Auth::user();
    $layout = $user->role->name === 'manager' ? 'manager-layout' : 'employee-layout';
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag(['title' => 'Hợp đồng của tôi', 'subtitle' => 'Xem lịch sử và tải file hợp đồng.'])">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Hợp đồng của tôi</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $employee->full_name }} · {{ $employee->employee_code }}</p>
            </div>
        </div>

        @if($activeContract)
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/50 p-5">
                <p class="text-xs font-bold uppercase text-emerald-600">Hợp đồng đang hiệu lực</p>
                <p class="mt-2 text-lg font-bold text-slate-800">{{ $activeContract->contract_code }}</p>
                <p class="text-sm text-slate-600">{{ $activeContract->contractType->contract_name ?? '—' }}</p>
                <a href="{{ route('employee.contracts.show', $activeContract) }}" class="mt-3 inline-block text-sm font-semibold text-emerald-700 hover:underline">Xem chi tiết →</a>
            </div>
        @endif

        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Mã HĐ</th>
                            <th class="px-5 py-3">Loại</th>
                            <th class="px-5 py-3">Thời hạn</th>
                            <th class="px-5 py-3">Trạng thái</th>
                            <th class="px-5 py-3 text-center">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $item)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->contract_code }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $item->contractType->contract_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ optional($item->start_date)->format('d/m/Y') }}
                                    → {{ optional($item->end_date)->format('d/m/Y') ?? 'Không TH' }}
                                </td>
                                <td class="px-5 py-3">@include('admin.contracts.partials.status-badge', ['contract' => $item])</td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('employee.contracts.show', $item) }}" class="font-semibold text-sky-600 hover:text-sky-700">Xem</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Chưa có hợp đồng.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contracts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $contracts->links() }}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>
