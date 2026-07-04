<x-admin-layout title="Kỳ lương đã xóa">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thùng rác kỳ lương</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Các kỳ lương đã bị xóa tạm thời — khôi phục sẽ khôi phục lại cả các phiếu lương liên kết.
                </p>
            </div>

            <a href="{{ route('admin.payroll-periods.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition text-sm">
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
                <h3 class="font-semibold text-slate-800">Kỳ lương trong thùng rác</h3>
                <span class="text-sm text-slate-500">{{ $periods->total() }} bản ghi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tháng/Năm</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Kỳ làm việc</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $loop->iteration + ($periods->currentPage() - 1) * $periods->perPage() }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $period->name }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    Tháng {{ $period->month }}/{{ $period->year }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $period->start_date?->format('d/m/Y') }} - {{ $period->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $period->deleted_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <form action="{{ route('admin.payroll-periods.restore', $period->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.payroll-periods.forceDelete', $period->id) }}" method="POST"
                                              onsubmit="return confirm('Xóa vĩnh viễn sẽ xóa hoàn toàn dữ liệu kỳ lương và tất cả phiếu lương liên kết. Bạn có chắc chắn muốn xóa?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-lg bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition">
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    Không có kỳ lương nào trong Thùng rác
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($periods->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $periods->links() }}
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
