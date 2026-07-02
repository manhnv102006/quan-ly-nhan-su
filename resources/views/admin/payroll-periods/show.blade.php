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
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                        🔵 Chưa tính lương (Open)
                    </span>
                    <form action="{{ route('admin.payroll-periods.calculate', $payrollPeriod) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-medium shadow-lg transition">
                            ⚡ Tính lương tự động
                        </button>
                    </form>
                @elseif ($payrollPeriod->status === 'calculated')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                        🟡 Đã tính lương (Calculated)
                    </span>
                    <form action="{{ route('admin.payroll-periods.approve', $payrollPeriod) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-lg transition">
                            ✅ Duyệt toàn bộ kỳ lương
                        </button>
                    </form>
                @elseif ($payrollPeriod->status === 'approved')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                        🟣 Đã duyệt (Approved)
                    </span>
                    <form action="{{ route('admin.payroll-periods.pay', $payrollPeriod) }}" method="POST"
                          onsubmit="return confirm('Xác nhận đã thực hiện chi trả lương cho toàn bộ nhân viên trong kỳ này?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow-lg transition">
                            💰 Chi trả toàn bộ kỳ lương
                        </button>
                    </form>
                @elseif ($payrollPeriod->status === 'paid')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        🟢 Đã chi trả (Paid)
                    </span>
                    <form action="{{ route('admin.payroll-periods.close', $payrollPeriod) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-medium shadow-lg transition">
                            🔒 Đóng kỳ lương
                        </button>
                    </form>
                @else
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        🔒 Đã đóng (Closed)
                    </span>
                @endif
            </div>
        </div>

        @if($payrollPeriod->approved_by || $payrollPeriod->paid_by)
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-wrap gap-6 text-xs text-slate-500">
                @if($payrollPeriod->approved_by)
                    <div class="flex items-center gap-1.5">
                        <span>✔️ <b>Người duyệt:</b> {{ $payrollPeriod->approver?->name }}</span>
                        <span>•</span>
                        <span>{{ $payrollPeriod->approved_at?->format('H:i d/m/Y') }}</span>
                    </div>
                @endif
                @if($payrollPeriod->paid_by)
                    <div class="flex items-center gap-1.5">
                        <span>💵 <b>Người chi trả:</b> {{ $payrollPeriod->payer?->name }}</span>
                        <span>•</span>
                        <span>{{ $payrollPeriod->paid_at?->format('H:i d/m/Y') }}</span>
                    </div>
                @endif
            </div>
        @endif

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

        <!-- Phân chia theo phòng ban -->
        <div class="mt-8">
            @include('admin.partials.department-cards', [
                'departmentSummaries' => $departmentSummaries,
                'routeName' => 'admin.payroll-periods.department',
                'routeParams' => ['payrollPeriod' => $payrollPeriod->id],
                'statLabels' => ['NV nhận lương', 'Tổng lương'],
                'statKeys' => ['employee_count', 'total_salary'],
                'statTones' => ['slate', 'violet'],
                'formatters' => [
                    'total_salary' => fn($val) => number_format($val, 0, ',', '.') . ' ₫'
                ]
            ])
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
