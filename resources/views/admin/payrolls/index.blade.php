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
                <a href="{{ route('admin.payroll-periods.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition text-sm">
                    📅 Quản lý kỳ lương
                </a>
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
                <p class="text-slate-500 text-sm">Tổng thực tế đã chi trả</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format(\App\Models\Payroll::whereHas('payrollPeriod', function($q) { $q->whereIn('status', ['paid', 'closed']); })->sum('total_salary'), 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng chưa chi trả</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">
                    {{ number_format(\App\Models\Payroll::whereHas('payrollPeriod', function($q) { $q->whereNotIn('status', ['paid', 'closed']); })->sum('total_salary'), 0, ',', '.') }} ₫
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
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thưởng (KPI)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Nghỉ phép</th>
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
                                    <a href="{{ route('admin.payroll-periods.show', $payroll->payroll_period_id) }}" class="text-violet-600 hover:underline">
                                        {{ $payroll->payrollPeriod?->name ?: '—' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->basic_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->allowance, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-medium text-emerald-600">
                                    @if($payroll->bonus > 0)+@endif{{ number_format($payroll->bonus, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <span class="text-emerald-600 font-semibold" title="Nghỉ phép có phép (hưởng lương)">{{ $payroll->paid_leave_days }}P</span> / 
                                    <span class="text-rose-500 font-semibold" title="Nghỉ phép không lương / vắng mặt">{{ $payroll->unpaid_leave_days }}KP</span>
                                </td>
                                <td class="px-6 py-4 text-red-500">
                                    -{{ number_format($payroll->deduction, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-bold text-violet-600">
                                    {{ number_format($payroll->total_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($payroll->payrollPeriod?->status === 'open')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">Chưa tính lương</span>
                                    @elseif ($payroll->payrollPeriod?->status === 'calculated')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Đã tính lương</span>
                                    @elseif ($payroll->payrollPeriod?->status === 'approved')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700">Đã duyệt</span>
                                    @elseif ($payroll->payrollPeriod?->status === 'paid')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đã chi trả</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Đã đóng</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('admin.payrolls.pdf', $payroll) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1"
                                           title="Xuất PDF">
                                            📄 PDF
                                        </a>
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
