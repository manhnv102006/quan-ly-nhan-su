<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Báo cáo chấm công {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #334155; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #1e293b; }
        .meta { color: #64748b; margin-bottom: 16px; }
        .stats { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .stats td { padding: 8px 10px; border: 1px solid #e2e8f0; }
        .stats .label { background: #f8fafc; font-weight: bold; width: 28%; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 6px 5px; text-align: center; }
        table.data th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        table.data td.left { text-align: left; }
        .badge-face { color: #4338ca; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Báo cáo chấm công — {{ $department->department_name }}</h1>
    <p class="meta">Tháng {{ $month }}/{{ $year }} · Mã phòng ban: {{ $department->department_code ?? '—' }}</p>

    <table class="stats">
        <tr>
            <td class="label">Đi làm</td><td>{{ $stats['present'] }}</td>
            <td class="label">Đi muộn</td><td>{{ $stats['late'] }}</td>
            <td class="label">Nghỉ phép</td><td>{{ $stats['leave'] }}</td>
        </tr>
        <tr>
            <td class="label">Vắng mặt</td><td>{{ $stats['absent'] }}</td>
            <td class="label">Tổng giờ làm</td><td>{{ number_format($stats['total_hours'], 2) }} giờ</td>
            <td class="label">Chấm công khuôn mặt</td><td>{{ $stats['face_count'] ?? 0 }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Ngày</th>
                <th>Ca</th>
                <th>Vào</th>
                <th>Ra</th>
                <th>Phương thức</th>
                <th>Tin cậy</th>
                <th>Liveness</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                @php
                    $checkIn = $attendance->check_in ?? $attendance->morning_check_in;
                    $checkOut = $attendance->check_out ?? $attendance->afternoon_check_out;
                @endphp
                <tr>
                    <td class="left">
                        {{ $attendance->employee?->full_name ?? '—' }}<br>
                        <small>{{ $attendance->employee?->employee_code ?? '' }}</small>
                    </td>
                    <td>{{ $attendance->attendance_date?->format('d/m/Y') }}</td>
                    <td>{{ $attendance->shift?->shift_name ?? '—' }}</td>
                    <td>{{ $checkIn ? $checkIn->format('H:i') : '—' }}</td>
                    <td>{{ $checkOut ? $checkOut->format('H:i') : '—' }}</td>
                    <td>
                        @if ($attendance->usesFaceRecognition())
                            <span class="badge-face">Khuôn mặt</span>
                        @else
                            Thủ công
                        @endif
                    </td>
                    <td>{{ $attendance->recognition_confidence !== null ? number_format($attendance->recognition_confidence, 2) : '—' }}</td>
                    <td>{{ $attendance->liveness_score !== null ? number_format($attendance->liveness_score, 2) : '—' }}</td>
                    <td>{{ $attendance->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Không có dữ liệu trong kỳ đã chọn.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
