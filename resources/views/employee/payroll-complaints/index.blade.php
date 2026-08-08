@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = ['title' => 'Khiếu nại lương', 'subtitle' => 'Theo dõi các khiếu nại về phiếu lương.'];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Khiếu nại lương của tôi</h1>
                <p class="text-xs text-slate-500 mt-1">Gửi khiếu nại nếu phát hiện lương, ngày công hoặc thưởng tính sai. Nếu được xác nhận, số tiền thiếu sẽ cộng vào bảng lương tháng sau.</p>
            </div>
            <a href="{{ route('employee.payroll-complaints.create') }}"
               class="inline-flex items-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                + Gửi khiếu nại
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                            <th class="px-6 py-4">Mã</th>
                            <th class="px-6 py-4">Kỳ lương</th>
                            <th class="px-6 py-4">Loại / Tiêu đề</th>
                            <th class="px-6 py-4 text-center">Trạng thái</th>
                            <th class="px-6 py-4 text-center">Ngày gửi</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($complaintList as $item)
                            @php $period = $item->payroll?->payrollPeriod; @endphp
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-700">{{ $item->complaint_code }}</td>
                                <td class="px-6 py-4">{{ $period?->name ?? '—' }}<br><span class="text-xs text-slate-400">{{ str_pad((string) ($period?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $period?->year ?? '—' }}</span></td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-slate-500">{{ $item->issueTypeLabel() }}</p>
                                    <p class="font-medium text-slate-800">{{ $item->subject }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">@include('payroll-complaints.partials.status-badge', ['complaint' => $item])</td>
                                <td class="px-6 py-4 text-center text-xs text-slate-400">{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('employee.payroll-complaints.show', $item) }}" class="text-xs font-semibold text-sky-600 hover:underline">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-14 text-center text-slate-400">Chưa có khiếu nại nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($complaintList->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">{{ $complaintList->links() }}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>
