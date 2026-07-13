<x-admin-layout title="Quản lý loại phụ cấp">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh mục loại phụ cấp</h2>
                <p class="text-sm text-slate-500 mt-1">Cấu hình mức mặc định — tự điền khi tạo/gia hạn hợp đồng.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Hợp đồng</a>
                <a href="{{ route('admin.allowance-types.create') }}" class="admin-btn-violet">+ Thêm loại phụ cấp</a>
            </div>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <form method="GET" class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="admin-field max-w-xs" placeholder="Tìm tên hoặc mã...">
                    <button type="submit" class="admin-btn-secondary">Tìm</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Tên</th>
                            <th class="px-5 py-3">Mã</th>
                            <th class="px-5 py-3">Mặc định</th>
                            <th class="px-5 py-3">Ghi chú tính lương</th>
                            <th class="px-5 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($allowanceTypes as $type)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    {{ $type->name }}
                                    @if($type->is_system)
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">Hệ thống</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $type->code }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800">{{ number_format($type->default_amount, 0, ',', '.') }}₫</td>
                                <td class="px-5 py-3 text-sm text-slate-500">{{ $type->calculation_note ?: '—' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $type->is_active ? 'Đang dùng' : 'Tắt' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('admin.allowance-types.edit', $type) }}" class="text-sm font-semibold text-violet-600 hover:text-violet-700">Sửa</a>
                                    @if(! $type->is_system)
                                        <form action="{{ route('admin.allowance-types.destroy', $type) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Xóa loại phụ cấp này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-sm font-semibold text-rose-600 hover:text-rose-700">Xóa</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Chưa có loại phụ cấp. Chạy seeder hoặc thêm mới.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($allowanceTypes->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $allowanceTypes->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
