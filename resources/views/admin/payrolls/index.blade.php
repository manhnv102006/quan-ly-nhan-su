<x-admin-layout title="Quản lý lương">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Bảng lương nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Xem và lọc thông tin thực lĩnh của nhân sự
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="openCalculateModal()"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition text-sm">
                    ⚡ Tính lương tự động
                </button>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng số phiếu lương</p>
                <h3 class="text-3xl font-bold mt-2">
                    {{ \App\Models\Payroll::count() }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã thanh toán (Paid)</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format(\App\Models\Payroll::where('status', 'paid')->sum('total_salary'), 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Chờ thanh toán (Approved)</p>
                <h3 class="text-3xl font-bold mt-2 text-blue-600">
                    {{ number_format(\App\Models\Payroll::where('status', 'approved')->sum('total_salary'), 0, ',', '.') }} ₫
                </h3>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ route('admin.payrolls') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-xs font-bold text-slate-500 uppercase mb-2">Tìm kiếm nhân viên</label>
                    <div class="relative">
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Nhập tên nhân viên..."
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <span class="absolute left-3 top-3.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div>
                    <label for="period_id" class="block text-xs font-bold text-slate-500 uppercase mb-2">Lọc theo kỳ lương</label>
                    <select id="period_id" name="period_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <option value="">-- Tất cả kỳ lương --</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 bg-violet-600 text-white font-medium px-5 py-3 rounded-xl hover:bg-violet-700 transition shadow-lg shadow-violet-500/10 text-sm">
                        Lọc kết quả
                    </button>
                    @if(request()->anyFilled(['search', 'period_id']))
                        <a href="{{ route('admin.payrolls') }}"
                           class="bg-slate-100 text-slate-700 font-medium px-5 py-3 rounded-xl hover:bg-slate-200 transition text-sm text-center">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bảng danh sách lương -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phụ cấp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Khấu trừ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thực lĩnh</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $payroll->employee?->employee_code ?: '—' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $payroll->employee?->full_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $payroll->payrollPeriod?->name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->basic_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->allowance + $payroll->bonus, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-red-500">
                                    -{{ number_format($payroll->deduction, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-bold text-violet-600">
                                    {{ number_format($payroll->total_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        @if ($payroll->isPaid())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                Đã trả
                                            </span>
                                        @elseif ($payroll->isApproved())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                Đã duyệt
                                            </span>
                                        @elseif ($payroll->isPending())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                                Chờ duyệt
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                Bản nháp
                                            </span>
                                        @endif

                                        @if ($payroll->approved_by && ($payroll->isApproved() || $payroll->isPaid()))
                                            <span class="text-[10px] text-slate-400 mt-1 block max-w-[120px] truncate"
                                                  title="Duyệt bởi {{ $payroll->approver?->name }} vào lúc {{ $payroll->approved_at?->format('H:i d/m/Y') }}">
                                                By: {{ $payroll->approver?->name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center">
                                        @if ($payroll->isDraft())
                                            <form action="{{ route('admin.payrolls.submit', $payroll) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition">
                                                    Gửi duyệt
                                                </button>
                                            </form>
                                        @elseif ($payroll->isPending())
                                            <form action="{{ route('admin.payrolls.approve', $payroll) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold transition">
                                                    Duyệt
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-emerald-500 font-medium text-xs">✓ Hoàn thành</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    Không tìm thấy dữ liệu bảng lương phù hợp
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payrolls->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Tính Lương -->
    <div id="calculate-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Tính lương tự động</h3>
            <p class="text-sm text-slate-500 mb-5">
                Chọn kỳ lương để tính toán. Hệ thống sẽ lấy thông tin lương cơ bản của nhân viên, số ngày đi làm/vắng mặt trong kỳ (từ chấm công), và kết quả đánh giá KPI để lập bảng lương nháp.
            </p>
            <form action="{{ route('admin.payrolls.generate') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label for="calc_period_id" class="block text-sm font-semibold text-slate-700 mb-2">Chọn kỳ lương cần chạy</label>
                    <select id="calc_period_id" name="payroll_period_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <option value="">-- Chọn kỳ lương đang mở --</option>
                        @foreach ($periods->where('status', 'open') as $period)
                            <option value="{{ $period->id }}">
                                {{ $period->name }} (Tháng {{ $period->month }}/{{ $period->year }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeCalculateModal()"
                            class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition text-sm">
                        Hủy
                    </button>
                    <button type="submit"
                            class="flex-1 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition text-sm">
                        Chạy tính toán
                    </button>
                </div>
            </form>
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
        function openCalculateModal() {
            const modal = document.getElementById('calculate-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCalculateModal() {
            const modal = document.getElementById('calculate-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('calculate-modal').addEventListener('click', function (e) {
            if (e.target === this) closeCalculateModal();
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
