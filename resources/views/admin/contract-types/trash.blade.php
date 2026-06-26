<x-admin-layout title="Loại hợp đồng đã xóa">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Loại hợp đồng đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">Khôi phục hoặc xóa vĩnh viễn loại hợp đồng.</p>
            </div>
            <a href="{{ route('admin.contract-types.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên loại hợp đồng</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thời hạn (tháng)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contractTypes as $type)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $type->id }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $type->contract_name }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $type->duration_month }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $type->deleted_at?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.contract-types.restore', $type->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">Khôi phục</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">Không có loại hợp đồng nào trong thùng rác.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($contractTypes->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $contractTypes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
