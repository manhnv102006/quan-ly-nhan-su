<x-accountant-layout title="Khiếu nại lương" subtitle="Xử lý khiếu nại tính lương sai của nhân viên.">
<div class="accountant-page space-y-6">
    @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([['Chờ QL', $stats['pending'], 'text-amber-600'], ['Chờ KT', $stats['processing'], 'text-sky-600'], ['Đã xử lý', $stats['resolved'], 'text-emerald-600'], ['Từ chối', $stats['rejected'], 'text-rose-600']] as [$label, $value, $tone])
            @include('accountant.partials.stat-card', ['label' => $label, 'value' => $value, 'tone' => $tone])
        @endforeach
    </div>

    <form method="GET" class="accountant-card flex flex-wrap items-end gap-3 p-5">
        <div class="min-w-[200px] flex-1"><label class="accountant-label">Tìm kiếm</label><input type="text" name="search" value="{{ request('search') }}" class="accountant-field" placeholder="Mã, tên NV..."></div>
        <div class="min-w-[160px]"><label class="accountant-label">Trạng thái</label>
            <select name="status" class="accountant-field">
                <option value="">Tất cả</option>
                @foreach(['pending'=>'Chờ quản lý','processing'=>'Chờ kế toán','resolved'=>'Đã xử lý','rejected'=>'Từ chối'] as $v=>$l)
                    <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="accountant-btn-primary">Lọc</button>
    </form>

    <div class="accountant-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead><tr class="bg-emerald-50/80 text-left text-xs font-bold uppercase text-slate-500">
                    <th class="px-4 py-3">Mã</th><th class="px-4 py-3">Nhân viên</th><th class="px-4 py-3">Kỳ lương</th><th class="px-4 py-3">Tiêu đề</th><th class="px-4 py-3 text-center">Trạng thái</th><th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($complaints as $item)
                        @php $period = $item->payroll?->payrollPeriod; @endphp
                        <tr class="hover:bg-emerald-50/30">
                            <td class="px-4 py-3 font-mono text-xs">{{ $item->complaint_code }}</td>
                            <td class="px-4 py-3"><p class="font-semibold">{{ $item->employee?->full_name }}</p><p class="text-xs text-slate-400">{{ $item->employee?->department?->department_name }}</p></td>
                            <td class="px-4 py-3">{{ $period?->name ?? '—' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $item->subject }}</td>
                            <td class="px-4 py-3 text-center">@include('payroll-complaints.partials.status-badge', ['complaint' => $item])</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('accountant.payroll-complaints.show', $item) }}" class="accountant-btn-secondary !py-1.5 !text-xs">Chi tiết</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">Không có khiếu nại.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())<div class="border-t px-5 py-4">{{ $complaints->links() }}</div>@endif
    </div>
</div>
</x-accountant-layout>
