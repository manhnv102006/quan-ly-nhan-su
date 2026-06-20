<x-admin-layout title="Hợp đồng đã xóa">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Hợp đồng đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">Khôi phục hợp đồng đã xóa mềm.</p>
            </div>
            <a href="{{ route('admin.contracts.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Loại hợp đồng</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600 font-semibold">{{ $contract->contract_code }}</td>
                                <td class="px-6 py-4 text-slate-800">{{ $contract->employee->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $contract->contractType->contract_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $contract->deleted_at?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.contracts.restore', $contract->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">Khôi phục</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">Không có hợp đồng nào trong thùng rác.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($contracts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
