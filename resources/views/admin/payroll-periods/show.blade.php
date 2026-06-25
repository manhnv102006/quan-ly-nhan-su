<x-admin-layout title="Chi tiết kỳ lương">

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.payroll-periods.index') }}" 
                       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition"
                       title="Quay lại danh sách kỳ lương">
                        ←
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800">Chi tiết kỳ lương: {{ $payrollPeriod->name }}</h2>
                </div>
                <p class="text-sm text-slate-500 mt-1.5 ml-12">
                    Thời gian: từ {{ $payrollPeriod->start_date?->format('d/m/Y') }} đến {{ $payrollPeriod->end_date?->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if ($payrollPeriod->status === 'open')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        🟢 Trạng thái: Đang mở (Open)
                    </span>
                @else
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        🔒 Trạng thái: Đã đóng (Closed)
                    </span>
                @endif
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng phiếu lương</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-800">{{ $stats['total_count'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng lương thực lĩnh</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">
                    {{ number_format($stats['total_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã chi trả (Paid)</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format($stats['paid_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Chưa chi trả (Unpaid)</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">
                    {{ number_format($stats['unpaid_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>
        </div>

        <!-- Danh sách bảng lương -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800">Danh sách lương nhân viên trong kỳ</h3>
                <span class="text-xs font-medium text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Hiển thị tối đa 10 nhân sự/trang
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phụ cấp & Thưởng</th>
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
                                    <div class="flex flex-col items-center justify-center text-center gap-1">
                                        @if ($payroll->isPaid())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                Đã chi trả
                                            </span>
                                            @if($payroll->approved_by)
                                                <span class="text-[10px] text-slate-400 block max-w-[150px] truncate"
                                                      title="Duyệt bởi {{ $payroll->approver?->name }} vào lúc {{ $payroll->approved_at?->format('H:i d/m/Y') }}">
                                                    Duyệt: {{ $payroll->approver?->name }}
                                                </span>
                                            @endif
                                            @if($payroll->paid_by)
                                                <span class="text-[10px] text-slate-500 block max-w-[150px] truncate"
                                                      title="Chi trả bởi {{ $payroll->payer?->name }} vào lúc {{ $payroll->paid_at?->format('H:i d/m/Y') }}">
                                                    Trả bởi: {{ $payroll->payer?->name }}
                                                </span>
                                            @endif
                                        @elseif ($payroll->isApproved())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                Chưa chi trả (Đã duyệt)
                                            </span>
                                            @if($payroll->approved_by)
                                                <span class="text-[10px] text-slate-400 block max-w-[150px] truncate"
                                                      title="Duyệt bởi {{ $payroll->approver?->name }} vào lúc {{ $payroll->approved_at?->format('H:i d/m/Y') }}">
                                                    Duyệt: {{ $payroll->approver?->name }}
                                                </span>
                                            @endif
                                        @elseif ($payroll->isPending())
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                                Chưa chi trả (Chờ duyệt)
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                Chưa chi trả (Nháp)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
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
                                        @elseif ($payroll->isApproved())
                                            <form action="{{ route('admin.payrolls.pay', $payroll) }}" method="POST"
                                                  onsubmit="return confirm('Xác nhận đã thực hiện chi trả lương cho nhân viên {{ $payroll->employee?->full_name }}?')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm shadow-emerald-500/10">
                                                    Chi trả
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-emerald-600 font-semibold text-xs flex items-center gap-1">
                                                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Đã chi trả
                                            </span>
                                        @endif
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
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    Kỳ lương này chưa được tính hoặc chưa có nhân sự nào được lập bảng lương.
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
