<x-manager-layout title="Khiếu nại lương" subtitle="Xem xét và chuyển kế toán xử lý khiếu nại của nhân viên.">
    <div class="manager-page w-full">
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>@endif

        <div class="manager-page-header">
            <div>
                <p class="manager-kicker">Lương</p>
                <h2 class="manager-title">Khiếu nại lương</h2>
                <p class="manager-subtitle">Chờ duyệt: <strong>{{ $pendingCount }}</strong></p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'Tất cả', 'pending' => 'Chờ duyệt', 'processing' => 'Chờ kế toán', 'resolved' => 'Đã xử lý', 'rejected' => 'Từ chối'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                   class="rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ request('status', '') == $val ? 'bg-teal-600 text-white border-teal-600' : 'bg-white text-slate-600 border-slate-200 hover:border-teal-200' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="manager-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                        <th class="px-6 py-4">Nhân viên</th><th class="px-6 py-4">Mã / Kỳ</th><th class="px-6 py-4">Tiêu đề</th><th class="px-6 py-4 text-center">Trạng thái</th><th class="px-6 py-4"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($complaints as $item)
                            @php $period = $item->payroll?->payrollPeriod; @endphp
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-4"><p class="font-semibold">{{ $item->employee?->full_name }}</p><p class="text-xs text-slate-400">{{ $item->employee?->employee_code }}</p></td>
                                <td class="px-6 py-4"><p class="font-mono text-xs">{{ $item->complaint_code }}</p><p class="text-xs text-slate-500">{{ $period?->name ?? '—' }}</p></td>
                                <td class="px-6 py-4 max-w-xs truncate" title="{{ $item->subject }}">{{ $item->subject }}</td>
                                <td class="px-6 py-4 text-center">@include('payroll-complaints.partials.status-badge', ['complaint' => $item])</td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('manager.payroll-complaints.show', $item) }}" class="text-xs font-semibold text-teal-700 hover:underline">Xử lý</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-14 text-center text-slate-400">Không có khiếu nại.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($complaints->hasPages())<div class="border-t px-6 py-4">{{ $complaints->links() }}</div>@endif
        </div>
    </div>
</x-manager-layout>
