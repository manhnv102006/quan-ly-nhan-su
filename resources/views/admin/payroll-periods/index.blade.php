<x-admin-layout title="Quản lý kỳ lương">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách kỳ lương</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Năm {{ $selectedYear }} · {{ $stats['total'] }}/12 kỳ đã tạo
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">

                <a href="{{ route('admin.payroll-periods.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition text-sm">
                    <span>+</span>
                    Thêm kỳ lương
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Đã tạo / 12 tháng</p>
                <h3 class="text-2xl font-bold mt-1">{{ $stats['total'] }}/12</h3>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-xs font-medium">Chưa tạo</p>
                <h3 class="text-2xl font-bold mt-1 text-slate-400">{{ $stats['missing'] }}</h3>
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
            <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Danh sách kỳ lương theo tháng</h3>
                    <p class="text-xs text-slate-500 mt-1">Hiển thị đủ 12 tháng của năm {{ $selectedYear }}</p>
                </div>

                <form action="{{ route('admin.payroll-periods.index') }}" method="GET" class="flex items-center gap-3">
                    <label for="year" class="text-sm font-semibold text-slate-600">Lọc theo năm</label>
                    <select id="year" name="year"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" @selected($year == $selectedYear)>Năm {{ $year }}</option>
                        @endforeach
                    </select>
                </form>
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
                        @foreach ($monthSlots as $slot)
                            @php($period = $slot['period'])
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition {{ $period ? '' : 'bg-slate-50/40' }}">
                                <td class="px-6 py-4 text-slate-600">{{ str_pad($slot['month'], 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 text-slate-600 font-semibold">
                                    @if ($period)
                                        BL{{ str_pad($period->id, 6, '0', STR_PAD_LEFT) }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $slot['name'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">Hàng tháng</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $slot['work_range'] }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                    @if ($period)
                                        {{ number_format($period->payrolls_sum_total_salary ?? 0, 0, ',', '.') }} ₫
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-emerald-600">
                                    @if ($period)
                                        {{ number_format($period->paid_salary_sum ?? 0, 0, ',', '.') }} ₫
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-rose-600">
                                    @if ($period)
                                        {{ number_format($period->unpaid_salary_sum ?? 0, 0, ',', '.') }} ₫
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($period)
                                        @if (!$period->is_active)
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã khóa</span>
                                        @elseif ($period->status === 'open')
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
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Chưa tạo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($period)
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

                                            <form action="{{ route('admin.payroll-periods.toggle-active', $period) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg {{ $period->is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} text-xs font-semibold transition"
                                                        title="{{ $period->is_active ? 'Khóa kỳ lương' : 'Mở khóa kỳ lương' }}">
                                                    @if ($period->is_active)
                                                        🔒 Khóa
                                                    @else
                                                        🔓 Mở khóa
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.payroll-periods.create', ['year' => $selectedYear, 'month' => $slot['month']]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-violet-50 text-violet-700 hover:bg-violet-100 text-xs font-semibold transition">
                                            + Tạo kỳ lương
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
