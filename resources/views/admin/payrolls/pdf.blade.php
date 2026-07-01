<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Phiếu lương nhân viên</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
        }
        .company-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .period {
            text-align: center;
            font-size: 14px;
            color: #475569;
            margin-bottom: 25px;
            font-style: italic;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info-table td {
            padding: 6px 0;
            vertical-align: top;
        }
        table.info-table td.label {
            width: 130px;
            font-weight: bold;
            color: #475569;
        }
        table.info-table td.value {
            color: #1e293b;
        }
        table.salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        table.salary-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            color: #334155;
        }
        table.salary-table td {
            border: 1px solid #cbd5e1;
            padding: 10px;
            color: #334155;
        }
        .text-right {
            text-align: right;
        }
        .text-red {
            color: #ef4444;
        }
        .total-row {
            background-color: #e0e7ff;
            font-weight: bold;
            font-size: 15px;
        }
        .total-row td {
            border: 2px solid #6366f1 !important;
            color: #4338ca !important;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signature-col {
            width: 50%;
            text-align: center;
            float: left;
        }
        .signature-title {
            font-weight: bold;
            color: #334155;
            margin-bottom: 60px;
        }
        .signature-name {
            font-weight: bold;
            color: #1e293b;
        }
        .footer {
            margin-top: 80px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">PeopleHub</div>
            <div class="company-info">
                Hệ thống Quản lý nhân sự chuyên nghiệp<br>
                Địa chỉ: Tòa nhà FPT Polytechnic, Trịnh Văn Bô, Nam Từ Liêm, Hà Nội<br>
                Điện thoại: (024) 7300 1955 - Email: contact@peoplehub.vn
            </div>
        </div>

        <!-- Title -->
        <div class="title">Phiếu chi tiết lương</div>
        <div class="period">Kỳ lương: {{ $payroll->payrollPeriod?->name ?: '—' }} (Tháng {{ $payroll->payrollPeriod?->month }}/{{ $payroll->payrollPeriod?->year }})</div>

        <!-- Thông tin nhân viên & thông tin nghiệp vụ -->
        <table class="info-table">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="section-title">Thông tin nhân viên</div>
                    <table style="width: 100%;">
                        <tr>
                            <td class="label">Mã nhân viên:</td>
                            <td class="value">{{ $payroll->employee?->employee_code ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Họ và tên:</td>
                            <td class="value" style="font-weight: bold;">{{ $payroll->employee?->full_name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Phòng ban:</td>
                            <td class="value">{{ $payroll->employee?->department?->name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Chức vụ:</td>
                            <td class="value">{{ $payroll->employee?->position?->name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Số ngày nghỉ:</td>
                            <td class="value">{{ $payroll->paid_leave_days }} phép / {{ $payroll->unpaid_leave_days }} vắng</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                    <div class="section-title">Thông tin giao dịch</div>
                    <table style="width: 100%;">
                        <tr>
                            <td class="label">Trạng thái:</td>
                            <td class="value" style="font-weight: bold;">
                                @if ($payroll->payrollPeriod?->isPaid())
                                    <span style="color: #10b981;">Đã chi trả</span>
                                @elseif ($payroll->payrollPeriod?->isApproved())
                                    <span style="color: #3b82f6;">Chưa chi trả (Đã duyệt)</span>
                                @elseif ($payroll->payrollPeriod?->isCalculated())
                                    <span style="color: #f59e0b;">Chưa chi trả (Chờ duyệt)</span>
                                @else
                                    <span style="color: #64748b;">Chưa chi trả (Bản nháp)</span>
                                @endif
                            </td>
                        </tr>
                        @if ($payroll->payrollPeriod?->approved_by)
                            <tr>
                                <td class="label">Người duyệt:</td>
                                <td class="value">{{ $payroll->payrollPeriod->approver?->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Ngày duyệt:</td>
                                <td class="value">{{ $payroll->payrollPeriod->approved_at?->format('H:i d/m/Y') }}</td>
                            </tr>
                        @endif
                        @if ($payroll->payrollPeriod?->paid_by)
                            <tr>
                                <td class="label">Người chi trả:</td>
                                <td class="value">{{ $payroll->payrollPeriod->payer?->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Ngày chi trả:</td>
                                <td class="value">{{ $payroll->payrollPeriod->paid_at?->format('H:i d/m/Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <!-- Bảng chi tiết lương -->
        <div class="section-title" style="margin-top: 10px;">Chi tiết các khoản lương</div>
        <table class="salary-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Khoản mục</th>
                    <th style="width: 40%; text-align: right;">Số tiền (VND)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Lương cơ bản</td>
                    <td class="text-right">{{ number_format($payroll->basic_salary, 0, ',', '.') }} ₫</td>
                </tr>
                <tr>
                    <td>Phụ cấp (Cố định hàng tháng)</td>
                    <td class="text-right">{{ number_format($payroll->allowance, 0, ',', '.') }} ₫</td>
                </tr>
                <tr>
                    <td>Thưởng KPI (Tính theo kết quả đánh giá)</td>
                    <td class="text-right">{{ number_format($payroll->bonus, 0, ',', '.') }} ₫</td>
                </tr>
                <tr class="text-red">
                    <td>Khấu trừ (Đi trễ, vắng mặt không phép)</td>
                    <td class="text-right">-{{ number_format($payroll->deduction, 0, ',', '.') }} ₫</td>
                </tr>
                <tr class="total-row">
                    <td>Thực lĩnh (Net Salary)</td>
                    <td class="text-right">{{ number_format($payroll->total_salary, 0, ',', '.') }} ₫</td>
                </tr>
            </tbody>
        </table>

        <!-- Chữ ký -->
        <div class="signatures">
            <div class="signature-col">
                <div class="signature-title">Người nhận lương</div>
                <div style="font-size: 11px; color: #666; margin-bottom: 50px;">(Ký và ghi rõ họ tên)</div>
                <div class="signature-name">{{ $payroll->employee?->full_name }}</div>
            </div>
            <div class="signature-col">
                <div class="signature-title">Người lập phiếu</div>
                <div style="font-size: 11px; color: #666; margin-bottom: 50px;">(Ký và ghi rõ họ tên)</div>
                <div class="signature-name">
                    {{ $payroll->payrollPeriod?->payer?->name ?: ($payroll->payrollPeriod?->approver?->name ?: 'Quản trị viên') }}
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Cảm ơn sự đóng góp của bạn cho công ty.<br>
            Phiếu lương này được tạo tự động bởi hệ thống PeopleHub vào lúc {{ now()->format('H:i:s d/m/Y') }}.
        </div>
    </div>
</body>
</html>
