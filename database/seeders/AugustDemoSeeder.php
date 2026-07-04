<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\PayrollPeriod;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AugustDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo kỳ lương tháng 8/2026 ở trạng thái open
        $period = PayrollPeriod::withTrashed()->updateOrCreate(
            [
                'month' => 8,
                'year' => 2026,
            ],
            [
                'name' => 'Kỳ lương tháng 08/2026',
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'status' => 'open',
                'deleted_at' => null,
            ]
        );

        // Xóa bảng lương cũ của kỳ này nếu có để không bị lỗi unique constraint
        DB::table('payrolls')->where('payroll_period_id', $period->id)->delete();

        // 2. Lấy danh sách phòng ban
        $departments = Department::all();
        if ($departments->isEmpty()) {
            // Tạo một số phòng ban mẫu
            $dept1 = Department::create(['department_name' => 'Công nghệ thông tin', 'department_code' => 'IT']);
            $dept2 = Department::create(['department_name' => 'Hành chính nhân sự', 'department_code' => 'HR']);
            $departments = collect([$dept1, $dept2]);
        }

        // Lấy danh sách chức vụ
        $position = Position::first() ?? Position::create([
            'position_name' => 'Nhân viên',
            'base_salary' => 12000000
        ]);

        // Lấy shift_id hợp lệ
        $shiftId = DB::table('shifts')->value('id') ?? 1;

        $startDate = Carbon::parse('2026-08-01');
        $endDate = Carbon::parse('2026-08-31');

        // Danh sách ngày đi làm chuẩn trong tháng 8/2026 (trừ Chủ nhật)
        $standardDaysList = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (!$current->isSunday()) {
                $standardDaysList[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        echo "August 2026 Standard working days: " . count($standardDaysList) . "\n";

        // 3. Với mỗi phòng ban, đảm bảo có ít nhất 5 nhân viên
        foreach ($departments as $dept) {
            echo "Seeding employees and attendances for department: {$dept->department_name}\n";
            
            // Xóa điểm danh, phép và đơn tăng ca cũ của kỳ tháng 8 trong phòng ban này
            $employeesInDept = Employee::where('department_id', $dept->id)->get();
            foreach ($employeesInDept as $emp) {
                DB::table('attendances')->where('employee_id', $emp->id)->whereBetween('attendance_date', ['2026-08-01', '2026-08-31'])->delete();
                DB::table('leave_requests')->where('employee_id', $emp->id)->whereBetween('start_date', ['2026-08-01', '2026-08-31'])->delete();
                DB::table('overtime_requests')->where('employee_id', $emp->id)->whereBetween('work_date', ['2026-08-01', '2026-08-31'])->delete();
            }

            // Tạo/cập nhật 5 nhân viên cho phòng ban này
            $empData = [
                ['code' => 'EMP_' . $dept->department_code . '_01', 'name' => 'Nguyễn Thị Đủ Công', 'case' => 1],
                ['code' => 'EMP_' . $dept->department_code . '_02', 'name' => 'Trần Văn Thiếu Công', 'case' => 2],
                ['code' => 'EMP_' . $dept->department_code . '_03', 'name' => 'Lê Hoàng Đi Trễ', 'case' => 3],
                ['code' => 'EMP_' . $dept->department_code . '_04', 'name' => 'Phạm Minh Nghỉ Phép', 'case' => 4],
                ['code' => 'EMP_' . $dept->department_code . '_05', 'name' => 'Vũ Tiến Không Phép', 'case' => 5],
            ];

            foreach ($empData as $index => $data) {
                $employee = Employee::updateOrCreate(
                    ['employee_code' => $data['code']],
                    [
                        'full_name' => $data['name'],
                        'department_id' => $dept->id,
                        'position_id' => $position->id,
                        'gender' => $index % 2 == 0 ? 'male' : 'female',
                        'date_of_birth' => '1995-01-01',
                        'email' => strtolower($data['code']) . '@example.com',
                        'phone' => '09' . rand(10000000, 99999999),
                        'address' => 'Hà Nội',
                        'hire_date' => '2025-01-01',
                        'status' => 'active',
                    ]
                );

                // Tạo hợp đồng active cho nhân viên để có lương cơ bản
                DB::table('contracts')->updateOrInsert(
                    ['employee_id' => $employee->id, 'status' => 'active'],
                    [
                        'contract_code' => 'HD_' . $employee->employee_code,
                        'contract_type_id' => 1,
                        'salary' => 15000000, // Lương cơ bản 15 triệu
                        'allowance' => 1500000,
                        'allowance_meal' => 700000,
                        'allowance_phone' => 400000,
                        'allowance_fuel' => 400000,
                        'start_date' => '2025-01-01',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // 4. Chấm công chi tiết theo Case của nhân viên
                $case = $data['case'];
                if ($case === 1) {
                    // Case 1: Đi làm đủ 26 ngày
                    foreach ($standardDaysList as $date) {
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => "$date 08:00:00",
                            'check_out' => "$date 17:00:00",
                            'work_hours' => 8.00,
                            'status' => 'present',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Thêm 2 đơn tăng ca cho nhân viên đủ công
                    DB::table('overtime_requests')->insert([
                        [
                            'employee_id' => $employee->id,
                            'work_date' => '2026-08-05',
                            'start_time' => '18:00',
                            'end_time' => '20:00',
                            'total_hours' => 2.00,
                            'reason' => 'Tăng ca hoàn thành task dự án',
                            'status' => 'completed',
                            'approved_by' => 1,
                            'approved_at' => '2026-08-05 17:30:00',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'employee_id' => $employee->id,
                            'work_date' => '2026-08-12',
                            'start_time' => '18:00',
                            'end_time' => '21:00',
                            'total_hours' => 3.00,
                            'reason' => 'Tăng ca fix bug hệ thống',
                            'status' => 'approved',
                            'approved_by' => 1,
                            'approved_at' => '2026-08-12 17:30:00',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                } elseif ($case === 2) {
                    // Case 2: Đi làm thiếu ngày công (ví dụ đi làm 20 ngày, 6 ngày vắng không phép)
                    // Hậu quả: Lương cơ bản tính theo 20/26 ngày, phụ cấp = 0
                    for ($i = 0; $i < 20; $i++) {
                        $date = $standardDaysList[$i];
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => "$date 08:00:00",
                            'check_out' => "$date 17:00:00",
                            'work_hours' => 8.00,
                            'status' => 'present',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    // 6 ngày vắng không phép
                    for ($i = 20; $i < 26; $i++) {
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $standardDaysList[$i],
                            'check_in' => null,
                            'check_out' => null,
                            'work_hours' => 0.00,
                            'status' => 'absent',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } elseif ($case === 3) {
                    // Case 3: Đi làm đủ 26 ngày nhưng có đi trễ 3 lần
                    // Hậu quả: Đủ công nhận phụ cấp, bị trừ phạt đi muộn 3 * 50k = 150k
                    foreach ($standardDaysList as $idx => $date) {
                        $status = ($idx < 3) ? 'late' : 'present';
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => $status === 'late' ? "$date 08:15:00" : "$date 08:00:00",
                            'check_out' => "$date 17:00:00",
                            'work_hours' => 8.00,
                            'status' => $status,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Thêm 1 đơn tăng ca hoàn thành
                    DB::table('overtime_requests')->insert([
                        'employee_id' => $employee->id,
                        'work_date' => '2026-08-15',
                        'start_time' => '18:00',
                        'end_time' => '20:30',
                        'total_hours' => 2.50,
                        'reason' => 'Tăng ca hỗ trợ triển khai dự án',
                        'status' => 'completed',
                        'approved_by' => 1,
                        'approved_at' => '2026-08-15 17:30:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($case === 4) {
                    // Case 4: Đi làm 24 ngày, nghỉ phép có lương 2 ngày (có đơn nghỉ phép đã duyệt)
                    // Hậu quả: Đủ công (24 đi làm + 2 phép = 26 công) -> Nhận đủ phụ cấp & 100% lương
                    for ($i = 0; $i < 24; $i++) {
                        $date = $standardDaysList[$i];
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => "$date 08:00:00",
                            'check_out' => "$date 17:00:00",
                            'work_hours' => 8.00,
                            'status' => 'present',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    // 2 ngày nghỉ phép
                    for ($i = 24; $i < 26; $i++) {
                        $date = $standardDaysList[$i];
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => null,
                            'check_out' => null,
                            'work_hours' => 0.00,
                            'status' => 'absent',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Tạo đơn nghỉ phép có lương
                        DB::table('leave_requests')->insert([
                            'employee_id' => $employee->id,
                            'leave_type' => 'annual',
                            'start_date' => $date,
                            'end_date' => $date,
                            'reason' => 'Nghỉ phép thường niên',
                            'total_days' => 1.0,
                            'status' => 'approved',
                            'approved_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } elseif ($case === 5) {
                    // Case 5: Đi làm 22 ngày, 4 ngày vắng không phép
                    // Hậu quả: Không đủ công -> phụ cấp = 0, lương tính pro-rata theo 22/26 ngày, phạt nghỉ không phép 4 * 300k = 1.2 triệu
                    for ($i = 0; $i < 22; $i++) {
                        $date = $standardDaysList[$i];
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $date,
                            'check_in' => "$date 08:00:00",
                            'check_out' => "$date 17:00:00",
                            'work_hours' => 8.00,
                            'status' => 'present',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    for ($i = 22; $i < 26; $i++) {
                        DB::table('attendances')->insert([
                            'employee_id' => $employee->id,
                            'shift_id' => $shiftId,
                            'attendance_date' => $standardDaysList[$i],
                            'check_in' => null,
                            'check_out' => null,
                            'work_hours' => 0.00,
                            'status' => 'absent',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        echo "August 2026 demo mock data seeded successfully!\n";
    }
}
