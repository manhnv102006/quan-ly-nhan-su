@php
    $hasFilters = filled(request('search'));
@endphp

<x-admin-layout title="Hợp đồng đã xóa">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thùng rác hợp đồng</h2>
                <p class="text-sm text-slate-500">Khôi phục hoặc xóa vĩnh viễn các hợp đồng đã xóa mềm.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Danh sách</a>
                <a href="{{ route('admin.contracts.create') }}" class="admin-btn-violet">Thêm hợp đồng</a>
            </div>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <form action="{{ route('admin.contracts.trashed') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[280px] flex-1">
                    <label for="search" class="admin-label">Tìm kiếm</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           placeholder="Mã HĐ hoặc tên nhân viên"
                           class="admin-field">
                </div>
                <button type="submit" class="admin-btn-primary">Tìm kiếm</button>
                @if($hasFilters)
                    <a href="{{ route('admin.contracts.trashed') }}" class="admin-btn-secondary">Xóa lọc</a>
                @endif
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Hợp đồng trong thùng rác</h3>
                <p class="text-xs text-slate-500">{{ $contracts->total() }} bản ghi · {{ $trashCount ?? $contracts->total() }} tổng</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Loại</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-4 text-sm font-bold text-slate-800">{{ $contract->contract_code }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->employee->full_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->contractType->contract_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-500">{{ $contract->deleted_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <a href="{{ route('admin.contracts.show', $contract->id) }}"
                                           class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                            Xem
                                        </a>
                                        <form method="POST" action="{{ route('admin.contracts.restore', $contract->id) }}" class="inline"
                                              onsubmit="return confirm('Khôi phục hợp đồng này?')">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.contracts.forceDelete', $contract->id) }}" class="inline"
                                              onsubmit="return confirm('Xóa vĩnh viễn hợp đồng này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">
                                    Không có hợp đồng nào trong thùng rác.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
