<x-admin-layout title="Khiếu nại lương">
<div class="admin-page space-y-6">
    <div class="admin-page-header">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý lương</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-800">Khiếu nại lương</h2>
            <p class="mt-1 text-sm text-slate-500">
                Theo dõi khiếu nại tính lương toàn công ty. Admin chỉ xem — kế toán xử lý khiếu nại.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([
            ['Chờ xử lý', $stats['awaiting'], 'text-sky-600'],
            ['Đã xử lý', $stats['resolved'], 'text-emerald-600'],
            ['Từ chối', $stats['rejected'], 'text-rose-600'],
        ] as [$label, $value, $tone])
            <div class="admin-card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="admin-card flex flex-wrap items-end gap-3 p-5">
        <div class="min-w-[200px] flex-1">
            <label class="admin-label">Tìm kiếm</label>
            <input type="text" name="search" value="{{ request('search') }}" class="admin-field" placeholder="Mã, tên NV...">
        </div>
        <div class="min-w-[160px]">
            <label class="admin-label">Trạng thái</label>
            <select name="status" class="admin-field">
                <option value="">Tất cả</option>
                @foreach(['processing' => 'Chờ kế toán', 'resolved' => 'Đã xử lý', 'rejected' => 'Từ chối'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="admin-btn-primary">Lọc</button>
    </form>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                        <th class="px-4 py-3">Mã</th>
                        <th class="px-4 py-3">Nhân viên</th>
                        <th class="px-4 py-3">Kỳ lương</th>
                        <th class="px-4 py-3">Tiêu đề</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($complaints as $item)
                        @php $period = $item->payroll?->payrollPeriod; @endphp
                        <tr class="hover:bg-violet-50/30">
                            <td class="px-4 py-3 font-mono text-xs">{{ $item->complaint_code }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $item->employee?->full_name }}</p>
                                <p class="text-xs text-slate-400">{{ $item->employee?->department?->department_name }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $period?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate px-4 py-3">{{ $item->subject }}</td>
                            <td class="px-4 py-3 text-center">@include('payroll-complaints.partials.status-badge', ['complaint' => $item])</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.payroll-complaints.show', $item) }}" class="admin-btn-secondary !py-1.5 !text-xs">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-slate-500">Không có khiếu nại.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
            <div class="border-t px-5 py-4">{{ $complaints->links() }}</div>
        @endif
    </div>
</div>
</x-admin-layout>
