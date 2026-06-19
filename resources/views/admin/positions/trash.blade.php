<x-admin-layout title="Chức vụ đã xóa">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách chức vụ đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Các chức vụ đã bị xóa mềm — có thể khôi phục hoặc xóa vĩnh viễn
                </p>
            </div>

            <a href="{{ route('admin.positions') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại danh sách
            </a>
        </div>

        @if (session('success'))
            <div id="success-toast"
                 class="flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-lg">
                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Chức vụ trong thùng rác</h3>
                <span class="text-sm text-slate-500">{{ $positions->total() }} bản ghi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">#</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên chức vụ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $loop->iteration + ($positions->currentPage() - 1) * $positions->perPage() }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $position->position_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-violet-50 text-violet-700 text-sm font-medium">
                                        {{ number_format($position->base_salary, 0, ',', '.') }} ₫
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($position->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Không hoạt động</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $position->deleted_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <form action="{{ route('admin.positions.restore', $position->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-lg bg-emerald-100 text-emerald-700 text-sm font-medium hover:bg-emerald-200 transition">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.positions.forceDelete', $position->id) }}" method="POST"
                                              onsubmit="return confirm('Xóa cứng sẽ xóa hoàn toàn dữ liệu và không thể khôi phục. Bạn chắc chắn không?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-lg bg-red-100 text-red-700 text-sm font-medium hover:bg-red-200 transition">
                                                Xóa cứng
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    Không có chức vụ nào đã xóa
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($positions->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $positions->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-admin-layout>
