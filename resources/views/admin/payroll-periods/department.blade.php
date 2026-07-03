<x-admin-layout title="Danh sách lương {{ $department->department_name }}">

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.payroll-periods.show', $payrollPeriod) }}" 
                       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition"
                       title="Quay lại chi tiết kỳ lương">
                        ←
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800">Lương phòng: {{ $department->department_name }}</h2>
                </div>
                <p class="text-sm text-slate-500 mt-1.5 ml-12">
                    Kỳ lương: {{ $payrollPeriod->name }} ({{ $payrollPeriod->start_date?->format('d/m/Y') }} - {{ $payrollPeriod->end_date?->format('d/m/Y') }})
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if ($departmentStatus === 'open')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                        🔵 Chưa tính lương (Open)
                    </span>
                    <form action="{{ route('admin.payroll-periods.calculate', $payrollPeriod) }}" method="POST">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-medium shadow-lg transition text-sm">
                            ⚡ Tính lương tự động
                        </button>
                    </form>
                @elseif ($departmentStatus === 'calculated')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                        🟡 Đã tính lương (Calculated)
                    </span>
                    <form action="{{ route('admin.payroll-periods.recalculate', $payrollPeriod) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn tính lại lương? Toàn bộ bảng lương hiện tại của kỳ này sẽ bị xóa và tính lại từ đầu.')">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-medium shadow-lg transition text-sm">
                            🔄 Tính lại
                        </button>
                    </form>
                    <form action="{{ route('admin.payroll-periods.approve', $payrollPeriod) }}" method="POST">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-lg transition text-sm">
                            ✅ Duyệt lương phòng ban
                        </button>
                    </form>
                @elseif ($departmentStatus === 'approved')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                        🟣 Đã duyệt (Approved)
                    </span>
                    <form action="{{ route('admin.payroll-periods.pay', $payrollPeriod) }}" method="POST"
                          onsubmit="return confirm('Xác nhận đã thực hiện chi trả lương cho nhân viên thuộc phòng ban này?')">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow-lg transition text-sm">
                            💰 Chi trả lương phòng ban
                        </button>
                    </form>
                @elseif ($departmentStatus === 'paid')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        🟢 Đã chi trả (Paid)
                    </span>
                    <form action="{{ route('admin.payroll-periods.close', $payrollPeriod) }}" method="POST">
                        @csrf
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-medium shadow-lg transition text-sm">
                            🔒 Đóng lương phòng ban
                        </button>
                    </form>
                @else
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        🔒 Đã đóng (Closed)
                    </span>
                @endif
            </div>
        </div>

        <!-- Danh sách bảng lương -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-semibold text-slate-800">Danh sách lương nhân viên</h3>
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
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phụ cấp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thưởng (KPI)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tăng ca</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Nghỉ phép</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Khấu trừ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thực lĩnh</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php
                                $lateDays = $payroll->employee?->attendances()
                                    ->whereBetween('attendance_date', [
                                        $payroll->payrollPeriod?->start_date,
                                        $payroll->payrollPeriod?->end_date
                                    ])
                                    ->where('status', 'late')
                                    ->count() ?? 0;
                            @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $payroll->employee?->employee_code ?: '—' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $payroll->employee?->full_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ number_format($payroll->basic_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ number_format($payroll->allowance, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    @if($payroll->bonus > 0)+@endif{{ number_format($payroll->bonus, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    @if($payroll->overtime_pay > 0)
                                        {{ number_format($payroll->overtime_pay, 0, ',', '.') }} ₫
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    <span class="font-semibold text-slate-700" title="Nghỉ phép có phép (hưởng lương)">{{ $payroll->paid_leave_days }}P</span> / 
                                    <span class="font-semibold text-slate-700" title="Nghỉ phép không lương / vắng mặt">{{ $payroll->unpaid_leave_days }}KP</span>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    -{{ number_format($payroll->deduction, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-950">
                                    {{ number_format($payroll->total_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center gap-2">
                                        <button type="button"
                                                onclick="openPayrollModal({{ json_encode([
                                                    'id' => $payroll->id,
                                                    'employee_code' => $payroll->employee?->employee_code ?: '—',
                                                    'full_name' => $payroll->employee?->full_name ?: '—',
                                                    'department_name' => $payroll->employee?->department?->department_name ?: '—',
                                                    'position_name' => $payroll->employee?->position?->position_name ?: '—',
                                                    'period_name' => $payroll->payrollPeriod?->name ?: '—',
                                                    'period_range' => ($payroll->payrollPeriod?->start_date?->format('d/m/Y') ?: '') . ' - ' . ($payroll->payrollPeriod?->end_date?->format('d/m/Y') ?: ''),
                                                    'basic_salary' => number_format($payroll->basic_salary, 0, ',', '.'),
                                                    'allowance' => number_format($payroll->allowance, 0, ',', '.'),
                                                    'allowance_meal' => number_format($payroll->allowance_meal, 0, ',', '.'),
                                                    'allowance_phone' => number_format($payroll->allowance_phone, 0, ',', '.'),
                                                    'allowance_fuel' => number_format($payroll->allowance_fuel, 0, ',', '.'),
                                                    'bonus' => number_format($payroll->bonus, 0, ',', '.'),
                                                    'overtime_hours' => $payroll->overtime_hours,
                                                    'overtime_pay' => number_format($payroll->overtime_pay, 0, ',', '.'),
                                                    'deduction' => number_format($payroll->deduction, 0, ',', '.'),
                                                    'late_days' => $lateDays,
                                                    'late_fine' => number_format($lateDays * 50000, 0, ',', '.'),
                                                    'unpaid_leave_fine' => number_format($payroll->unpaid_leave_days * 300000, 0, ',', '.'),
                                                    'standard_working_days' => $payroll->standard_working_days,
                                                    'actual_working_days' => $payroll->actual_working_days,
                                                    'total_salary' => number_format($payroll->total_salary, 0, ',', '.'),
                                                    'paid_salary' => in_array($payroll->status, ['paid', 'closed']) ? number_format($payroll->total_salary, 0, ',', '.') : '0',
                                                    'remaining_salary' => !in_array($payroll->status, ['paid', 'closed']) ? number_format($payroll->total_salary, 0, ',', '.') : '0',
                                                    'status_label' => match ($payroll->status) {
                                                        'calculated' => 'Đã tính lương',
                                                        'approved' => 'Đã duyệt',
                                                        'paid' => 'Đã chi trả',
                                                        'closed' => 'Đã đóng',
                                                        default => 'Chưa tính lương'
                                                    },
                                                    'paid_leave_days' => $payroll->paid_leave_days,
                                                    'unpaid_leave_days' => $payroll->unpaid_leave_days,
                                                    'pdf_url' => route('admin.payrolls.pdf', $payroll),
                                                ]) }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-semibold transition flex items-center gap-1"
                                                title="Xem chi tiết">
                                            👁️ Xem chi tiết
                                        </button>
                                        <a href="{{ route('admin.payrolls.pdf', $payroll) }}"
                                           class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1"
                                           title="Xuất PDF">
                                            📄 Xuất PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-500">
                                    Kỳ lương này chưa được tính hoặc chưa có nhân sự nào trong phòng ban này được lập bảng lương.
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
    <!-- Modal Chi tiết Phiếu lương -->
    <div id="payrollDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closePayrollModal()"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-4xl transform rounded-3xl bg-white p-6 shadow-2xl transition-all border border-slate-100">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span>Phiếu lương cá nhân</span>
                            <span id="modalPayrollCode" class="text-violet-600 bg-violet-50 px-2 py-0.5 rounded-lg text-sm font-semibold">PL000000</span>
                        </h3>
                    </div>
                    <button onclick="closePayrollModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-100 mb-6">
                    <button onclick="switchTab('payment')" id="tab-payment" class="px-4 py-2 border-b-2 border-violet-600 text-violet-600 font-semibold text-sm transition">
                        Thanh toán
                    </button>
                    <button onclick="switchTab('attendance')" id="tab-attendance" class="px-4 py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-semibold text-sm transition">
                        Chấm công chi tiết
                    </button>
                </div>

                <!-- Tab: Thanh toán -->
                <div id="content-payment" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left column -->
                    <div class="space-y-4 text-sm text-slate-600">
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Mã & Họ tên:</span>
                            <span class="font-semibold text-slate-800" id="modalEmpName">NV000002 - Tùng Sơn</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Phòng ban:</span>
                            <span class="font-semibold text-slate-800" id="modalEmpDept">Kế toán</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Chức danh:</span>
                            <span class="font-semibold text-slate-800" id="modalEmpPosition">Nhân viên</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Loại lương chính:</span>
                            <span class="font-semibold text-slate-800">Cố định</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Trạng thái:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-600 border border-violet-100" id="modalStatus">Đã chốt lương</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Bảng lương:</span>
                            <span class="font-semibold text-slate-800" id="modalPeriodName">Bảng lương tháng 12/2025</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Kỳ làm việc:</span>
                            <span class="font-semibold text-slate-800" id="modalPeriodRange">01/12/2025 - 31/12/2025</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Ngày công chuẩn:</span>
                            <span class="font-semibold text-slate-800" id="modalStandardDays">26</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Ngày công thực tế:</span>
                            <span class="font-semibold text-emerald-600" id="modalActualDays">26</span>
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="bg-slate-50/50 rounded-2xl p-5 border border-slate-100 space-y-3.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium" id="modalBasicLabel">Lương chính:</span>
                            <span class="font-bold text-slate-800" id="modalBasicSalary">11,000,000 ₫</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Phụ cấp:</span>
                            <span class="font-bold text-slate-800" id="modalAllowance">500,000 ₫</span>
                        </div>
                        <div class="pl-4 space-y-1 text-xs text-slate-500 border-l border-slate-200">
                            <div class="flex justify-between items-center">
                                <span>+ Ăn trưa:</span>
                                <span id="modalAllowanceMeal">0 ₫</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>+ Điện thoại:</span>
                                <span id="modalAllowancePhone">0 ₫</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>+ Xăng xe:</span>
                                <span id="modalAllowanceFuel">0 ₫</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Thưởng (KPI):</span>
                            <span class="font-bold text-slate-800 text-emerald-600" id="modalBonus">0 ₫</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Tăng ca:</span>
                            <span class="font-bold text-slate-800 text-emerald-600" id="modalOvertime">0 ₫</span>
                        </div>
                        <div class="border-t border-slate-200/60 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 font-bold">Tổng thu nhập:</span>
                            <span class="font-extrabold text-slate-800" id="modalTotalIncome">11,500,000 ₫</span>
                        </div>
                        <div class="flex justify-between items-center text-rose-500">
                            <span class="font-medium" id="modalLateLabel">Phạt đi muộn (0 lần):</span>
                            <span class="font-bold" id="modalLateFine">-0 ₫</span>
                        </div>
                        <div class="flex justify-between items-center text-rose-500">
                            <span class="font-medium" id="modalLeaveLabel">Phạt nghỉ quá phép/không phép (0 ngày):</span>
                            <span class="font-bold" id="modalLeaveFine">-0 ₫</span>
                        </div>
                        <div class="flex justify-between items-center font-bold text-rose-600 border-t border-slate-200/60 pt-2">
                            <span>Tổng giảm trừ:</span>
                            <span id="modalDeduction">-0 ₫</span>
                        </div>
                        <div class="border-t border-slate-200/60 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-violet-700 font-bold text-base">Tổng lương (Thực lĩnh):</span>
                            <span class="font-black text-violet-700 text-lg" id="modalTotalSalary">11,050,000 ₫</span>
                        </div>
                        <div class="border-t border-slate-200/60 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Đã trả nhân viên:</span>
                            <span class="font-bold text-emerald-600" id="modalPaidSalary">11,050,000 ₫</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Còn cần trả:</span>
                            <span class="font-bold text-rose-600" id="modalRemainingSalary">0 ₫</span>
                        </div>
                    </div>
                </div>

                 <!-- Tab: Chấm công chi tiết -->
                <div id="content-attendance" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-violet-50/50 rounded-2xl p-4 border border-violet-100 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ngày công chuẩn</p>
                            <h4 class="text-2xl font-black text-violet-700 mt-1" id="modalStandardDaysAtt">26</h4>
                        </div>
                        <div class="bg-sky-50/50 rounded-2xl p-4 border border-sky-100 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ngày công thực tế</p>
                            <h4 class="text-2xl font-black text-sky-700 mt-1" id="modalActualDaysAtt">26</h4>
                        </div>
                        <div class="bg-emerald-50/50 rounded-2xl p-4 border border-emerald-100 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nghỉ phép hưởng lương</p>
                            <h4 class="text-2xl font-black text-emerald-700 mt-1" id="modalPaidLeave">0 ngày</h4>
                        </div>
                        <div class="bg-rose-50/50 rounded-2xl p-4 border border-rose-100 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nghỉ không lương / Vắng</p>
                            <h4 class="text-2xl font-black text-rose-700 mt-1" id="modalUnpaidLeave">0 ngày</h4>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                        <h4 class="font-bold text-slate-800 mb-3 text-sm flex items-center gap-1.5">
                            ⏱️ Thống kê số giờ tăng ca
                        </h4>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600">Tổng số giờ làm thêm trong kỳ:</span>
                            <span class="font-bold text-slate-800" id="modalOvertimeHours">0 giờ</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4 mt-6">
                    <a id="modalPdfBtn" href="#" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium transition text-sm">
                        📄 Xuất file PDF
                    </a>
                    <button onclick="closePayrollModal()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition text-sm">
                        Bỏ qua
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script điều khiển Modal -->
    <script>
        let currentTab = 'payment';

        function openPayrollModal(data) {
            document.getElementById('modalPayrollCode').innerText = 'PL' + String(data.id).padStart(6, '0');
             document.getElementById('modalBasicLabel').innerText = 'Lương chính (' + data.actual_working_days + '/' + data.standard_working_days + ' ngày):';
            document.getElementById('modalBasicSalary').innerText = data.basic_salary + ' ₫';
            document.getElementById('modalStandardDays').innerText = data.standard_working_days;
            document.getElementById('modalActualDays').innerText = data.actual_working_days;
            document.getElementById('modalAllowance').innerText = data.allowance + ' ₫';
            document.getElementById('modalAllowanceMeal').innerText = data.allowance_meal + ' ₫';
            document.getElementById('modalAllowancePhone').innerText = data.allowance_phone + ' ₫';
            document.getElementById('modalAllowanceFuel').innerText = data.allowance_fuel + ' ₫';
            document.getElementById('modalBonus').innerText = data.bonus + ' ₫';
            document.getElementById('modalOvertime').innerText = data.overtime_pay + ' ₫';
            
            // Tính tổng thu nhập
            let basic = parseFloat(data.basic_salary.replace(/\./g, ''));
            let allowance = parseFloat(data.allowance.replace(/\./g, ''));
            let bonus = parseFloat(data.bonus.replace(/\./g, ''));
            let overtime = parseFloat(data.overtime_pay.replace(/\./g, ''));
            let totalIncome = basic + allowance + bonus + overtime;
            
            document.getElementById('modalTotalIncome').innerText = totalIncome.toLocaleString('vi-VN') + ' ₫';
            document.getElementById('modalLateLabel').innerText = 'Phạt đi muộn (' + data.late_days + ' lần):';
            document.getElementById('modalLateFine').innerText = '-' + data.late_fine + ' ₫';
            document.getElementById('modalLeaveLabel').innerText = 'Phạt nghỉ quá phép (' + data.unpaid_leave_days + ' ngày):';
            document.getElementById('modalLeaveFine').innerText = '-' + data.unpaid_leave_fine + ' ₫';
            document.getElementById('modalDeduction').innerText = '-' + data.deduction + ' ₫';
            document.getElementById('modalTotalSalary').innerText = data.total_salary + ' ₫';
            document.getElementById('modalPaidSalary').innerText = data.paid_salary + ' ₫';
            document.getElementById('modalRemainingSalary').innerText = data.remaining_salary + ' ₫';
 
            // Attendance tab data
            document.getElementById('modalStandardDaysAtt').innerText = data.standard_working_days;
            document.getElementById('modalActualDaysAtt').innerText = data.actual_working_days;
            document.getElementById('modalPaidLeave').innerText = data.paid_leave_days + ' ngày';
            document.getElementById('modalUnpaidLeave').innerText = data.unpaid_leave_days + ' ngày';
            document.getElementById('modalOvertimeHours').innerText = data.overtime_hours + ' giờ';
 
            // PDF button url
            document.getElementById('modalPdfBtn').href = data.pdf_url;

            // Reset tab
            switchTab('payment');

            // Show Modal
            document.getElementById('payrollDetailModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePayrollModal() {
            document.getElementById('payrollDetailModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function switchTab(tab) {
            currentTab = tab;
            const btnPayment = document.getElementById('tab-payment');
            const btnAttendance = document.getElementById('tab-attendance');
            const contentPayment = document.getElementById('content-payment');
            const contentAttendance = document.getElementById('content-attendance');

            if (tab === 'payment') {
                btnPayment.className = "px-4 py-2 border-b-2 border-violet-600 text-violet-600 font-semibold text-sm transition";
                btnAttendance.className = "px-4 py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-semibold text-sm transition";
                contentPayment.classList.remove('hidden');
                contentAttendance.classList.add('hidden');
            } else {
                btnPayment.className = "px-4 py-2 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-semibold text-sm transition";
                btnAttendance.className = "px-4 py-2 border-b-2 border-violet-600 text-violet-600 font-semibold text-sm transition";
                contentPayment.classList.add('hidden');
                contentAttendance.classList.remove('hidden');
            }
        }
    </script>

</x-admin-layout>
