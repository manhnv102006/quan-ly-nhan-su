<?php

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

// Lấy kỳ lương tháng 7/2026
$period = PayrollPeriod::where('month', 7)->where('year', 2026)->first();

if (!$period) {
    echo "Không tìm thấy kỳ lương tháng 7/2026.\n";
    exit;
}

// Lấy nhân viên số 1 (ví dụ)
$employee = Employee::first();

if (!$employee) {
    echo "Không tìm thấy nhân viên nào.\n";
    exit;
}

echo "Tạo dữ liệu nghỉ không phép cho nhân viên: " . $employee->full_name . "\n";

// Xóa các record chấm công cũ trong tháng 7 để làm lại cho sạch
Attendance::where('employee_id', $employee->id)
    ->whereBetween('attendance_date', [$period->start_date, $period->end_date])
    ->delete();

// Tạo 5 ngày nghỉ không phép (absent)
$absentDates = [
    '2026-07-01', '2026-07-02', '2026-07-03', '2026-07-04', '2026-07-06'
];

foreach ($absentDates as $date) {
    Attendance::create([
        'employee_id' => $employee->id,
        'attendance_date' => $date,
        'status' => 'absent',
        'shift_id' => 1,
    ]);
}

// Các ngày còn lại từ mùng 7 đến 31 đi làm đầy đủ (present)
$start = Carbon::parse('2026-07-07');
$end = Carbon::parse('2026-07-31');

for ($d = $start; $d->lte($end); $d->addDay()) {
    if (!$d->isSunday()) {
        Attendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => $d->format('Y-m-d'),
            'status' => 'present',
            'shift_id' => 1,
        ]);
    }
}

echo "Đã tạo 5 ngày nghỉ không phép và " . (27 - 5) . " ngày đi làm.\n";
echo "Vui lòng vào Quản lý kỳ lương -> Chốt lương để xem kết quả cảnh báo!\n";
