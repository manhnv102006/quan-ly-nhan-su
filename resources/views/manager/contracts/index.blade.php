<x-manager-layout title="Hợp đồng phòng ban" subtitle="Xem hợp đồng nhân viên trong phạm vi quản lý (chỉ đọc).">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Hợp đồng phòng ban</h2>
                <p class="text-sm text-slate-500 mt-1">Theo dõi hợp đồng sắp hết hạn và lịch sử nhân viên.</p>
            </div>
            @if($expiringCount > 0)
                <a href="{{ route('manager.contracts.index', ['expiring' => 1]) }}"
                   class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800">
                    {{ $expiringCount }} HĐ sắp hết hạn (30 ngày)
                </a>
            @endif
        </div>

        <div class="admin-card p-5">
            <form method="GET" class="flex flex-wrap gap-3">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="admin-field max-w-xs" placeholder="Mã HĐ / tên NV...">
                <select name="status" class="admin-field max-w-[180px]">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="expiring" value="1" @checked($filters['expiring'] ?? false)> Sắp hết hạn
                </label>
                <button type="submit" class="admin-btn-secondary">Lọc</button>
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Mã HĐ</th>
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Loại</th>
                            <th class="px-5 py-3">Thời hạn</th>
                            <th class="px-5 py-3">Trạng thái</th>
                            <th class="px-5 py-3 text-center">Xem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $item)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->contract_code }}</td>
                                <td class="px-5 py-3">{{ $item->employee->full_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $item->contractType->contract_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ optional($item->start_date)->format('d/m/Y') }}
                                    → {{ optional($item->end_date)->format('d/m/Y') ?? 'Không TH' }}
                                </td>
                                <td class="px-5 py-3">@include('admin.contracts.partials.status-badge', ['contract' => $item])</td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('manager.contracts.show', $item) }}" class="font-semibold text-violet-600">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Không có hợp đồng.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contracts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $contracts->links() }}</div>
            @endif
        </div>
    </div>
</x-manager-layout>
