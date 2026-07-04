<x-admin-layout title="Quản lý kỳ lương">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách kỳ lương</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $stats['total'] }} kỳ lương
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.payroll-periods.trash') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition text-sm">
                    🗑️ Thùng rác ({{ \App\Models\PayrollPeriod::onlyTrashed()->count() }})
                </a>
                <a href="{{ route('admin.payroll-periods.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition text-sm">
                    <span>+</span>
                    Thêm kỳ lương
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Tổng kỳ lương</p>
                <h3 class="text-2xl font-bold mt-1">{{ $stats['total'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Chưa tính (Open)</p>
                <h3 class="text-2xl font-bold mt-1 text-sky-600">{{ $stats['open'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Đã tính (Calculated)</p>
                <h3 class="text-2xl font-bold mt-1 text-amber-600">{{ $stats['calculated'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Đã duyệt (Approved)</p>
                <h3 class="text-2xl font-bold mt-1 text-violet-600">{{ $stats['approved'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Đã chi trả (Paid)</p>
                <h3 class="text-2xl font-bold mt-1 text-emerald-600">{{ $stats['paid'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Đã đóng (Closed)</p>
                <h3 class="text-2xl font-bold mt-1 text-slate-500">{{ $stats['closed'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách kỳ lương</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Kỳ hạn trả</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Kỳ làm việc</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500">Tổng lương</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500">Đã trả nhân viên</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500">Còn cần trả</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ ($periods->currentPage() - 1) * $periods->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 text-slate-600 font-semibold">BL{{ str_pad($period->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $period->name }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">Hàng tháng</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $period->start_date?->format('d/m/Y') }} - {{ $period->end_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    {{ number_format($period->payrolls_sum_total_salary ?? 0, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-emerald-600">
                                    {{ number_format($period->paid_salary_sum ?? 0, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-rose-600">
                                    {{ number_format($period->unpaid_salary_sum ?? 0, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($period->status === 'open')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">Tạm tính</span>
                                    @elseif ($period->status === 'calculated')
                                        @if ($period->is_all_calculated)
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Đã tính xong</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Chưa tính xong</span>
                                        @endif
                                    @elseif ($period->status === 'approved')
                                        @if ($period->is_all_approved)
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700">Đã duyệt xong</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-fuchsia-100 text-fuchsia-700">Chưa duyệt xong</span>
                                        @endif
                                    @elseif ($period->status === 'paid')
                                        @if ($period->is_all_paid)
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đã chi trả</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">Chưa chi trả xong</span>
                                        @endif
                                    @else
                                        @if ($period->is_all_closed)
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Đã đóng</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-600">Chưa đóng xong</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('admin.payroll-periods.show', $period) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-semibold transition"
                                           title="Xem chi tiết">
                                            👁️ Xem chi tiết
                                        </a>

                                        <a href="{{ route('admin.payroll-periods.edit', $period) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 text-xs font-semibold transition"
                                           title="Chỉnh sửa kỳ lương">
                                            ✏️ Chỉnh sửa
                                        </a>

                                        <form action="{{ route('admin.payroll-periods.destroy', $period) }}"
                                              method="POST"
                                              id="delete-form-{{ $period->id }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    data-id="{{ $period->id }}"
                                                    data-name="{{ $period->name }}"
                                                    onclick="triggerDelete(this)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition"
                                                    title="Xóa kỳ lương">
                                                🗑️ Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-12 text-slate-400">
                                    Chưa có kỳ lương nào được tạo
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

    <!-- Modal Xóa -->
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa kỳ lương?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc chắn muốn xóa
                <span id="delete-period-name" class="font-semibold text-slate-700"></span>?
                Hành động này không thể hoàn tác nếu không có bản sao lưu.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" onclick="confirmDelete()"
                        class="flex-1 px-5 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition">
                    Xóa
                </button>
            </div>
        </div>
    </div>

    <!-- Thông báo Success -->
    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Thông báo Error -->
    @if (session('error'))
        <div id="error-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
        </div>
    @endif

    <script>
        let deleteTargetId = null;

        function triggerDelete(btn) {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            openDeleteModal(id, name);
        }

        function openDeleteModal(id, name) {
            deleteTargetId = id;
            document.getElementById('delete-period-name').textContent = name;
            const modal = document.getElementById('delete-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            deleteTargetId = null;
        }

        function confirmDelete() {
            if (deleteTargetId) {
                document.getElementById('delete-form-' + deleteTargetId).submit();
            }
        }

        document.getElementById('delete-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });

        // Tự tắt Toast thông báo sau 4 giây
        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }

        const errorToast = document.getElementById('error-toast');
        if (errorToast) {
            setTimeout(function () {
                errorToast.style.transition = 'opacity 0.3s ease';
                errorToast.style.opacity = '0';
                setTimeout(function () { errorToast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-admin-layout>
