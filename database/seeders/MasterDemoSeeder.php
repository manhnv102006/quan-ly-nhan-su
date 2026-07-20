<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Dữ liệu demo đầy đủ để test TẤT CẢ chức năng:
 *   Admin · Manager · Employee · Lương · KPI · Nghỉ phép · Tăng ca · Tuyển dụng · Thông báo
 *
 * Chạy: php artisan db:seed --class=MasterDemoSeeder
 *
 * Tài khoản sau seed:
 * ┌──────────────┬──────────┬────────────────────────────────────┐
 * │ Username     │ Password │ Ghi chú                            │
 * ├──────────────┼──────────┼────────────────────────────────────┤
 * │ admin        │ password │ Admin toàn hệ thống                │
 * │ manager      │ password │ Trưởng phòng IT (EMP002)           │
 * │ manager_sale │ password │ Trưởng phòng Kinh doanh (EMP006)   │
 * │ employee     │ password │ Nhân viên IT Phạm Thị Dung (EMP004)│
 * │ emp_it02     │ password │ Nhân viên IT Trương Quốc Bảo(EMP003)│
 * │ emp_sale01   │ password │ Nhân viên KD Nguyễn T Bích Ngọc   │
 * └──────────────┴──────────┴────────────────────────────────────┘
 */
class MasterDemoSeeder extends Seeder
{
    private int $adminUserId = 1;

    private int $defaultShiftId = 1;

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->truncateTables();
        Schema::enableForeignKeyConstraints();

        // ── Nền tảng ──────────────────────────────────────────────
        $this->seedRoles();
        $this->seedUsers();
        $this->seedPositions();
        $this->seedDepartments();
        $this->seedShifts();
        $this->seedContractTypes();

        $this->adminUserId    = (int) DB::table('users')->where('username', 'admin')->value('id');
        $this->defaultShiftId = (int) DB::table('shifts')->value('id');

        // ── Nhân sự ──────────────────────────────────────────────
        $this->seedEmployees();
        $this->linkUsersToEmployees();
        $this->seedContracts();
        $this->call(AllowanceTypeSeeder::class);
        $this->seedDocuments();

        // ── Chấm công ─────────────────────────────────────────────
        $this->seedEmployeeShifts();
        $this->seedAttendances();
        $this->seedTodayAttendance();

        // ── Nghỉ phép ─────────────────────────────────────────────
        $this->seedLeaveRequests();

        // ── Tăng ca ───────────────────────────────────────────────
        $this->seedOvertimeRequests();

        // ── KPI ───────────────────────────────────────────────────
        $this->seedKPIs();
        $this->seedKPIAssignments();
        $this->seedEmployeeKPIs();

        // ── Bảng lương ────────────────────────────────────────────
        $this->seedPayrollPeriods();
        $this->calculatePayrolls();

        // ── Tuyển dụng ────────────────────────────────────────────
        $this->seedRecruitment();

        // ── Thông báo ─────────────────────────────────────────────
        $this->seedNotifications();

        // ── Bổ sung module mới ────────────────────────────────────
        $this->seedHolidays();
        $this->seedSalaryAdvances();
        $this->seedEarlyLeaveRequests();
        $this->seedInsurances();
        $this->seedTaxProfiles();
        $this->seedFaceDescriptors();

        $this->printSummary();
    }

    // ═══════════════════════════════════════════════════════════════
    // TRUNCATE (thứ tự ngược chiều FK)
    // ═══════════════════════════════════════════════════════════════
    private function truncateTables(): void
    {
        $tables = [
            'salary_advance_deductions',
            'salary_advances',
            'early_leave_requests',
            'employee_face_descriptors',
            'employee_insurances',
            'tax_dependents',
            'employee_tax_profiles',
            'holidays',
            'contract_allowances',
            'allowance_types',
            'team_membership_requests',
            'notification_users',
            'notifications',
            'interviews',
            'candidates',
            'job_posts',
            'payrolls',
            'payroll_periods',
            'employee_kpis',
            'kpi_assignments',
            'kpi_tasks',
            'kpi_department',
            'kpis',
            'overtime_request_histories',
            'overtime_requests',
            'leave_request_histories',
            'leave_requests',
            'attendances',
            'employee_shifts',
            'department_transfers',
            'employee_documents',
            'contract_extensions',
            'contract_terminations',
            'contracts',
            'employees',
            'departments',
            'positions',
            'shifts',
            'contract_types',
            'users',
            'roles',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // NỀN TẢNG
    // ═══════════════════════════════════════════════════════════════
    private function seedRoles(): void
    {
        $this->call(RoleSeeder::class);
    }

    private function seedUsers(): void
    {
        $rid = fn (string $name) => DB::table('roles')->where('name', $name)->value('id');

        $users = [
            ['admin',        'Nguyễn Quản Trị',       'admin@example.com',        $rid('admin')],
            ['manager',      'Lê Văn Thành',            'manager@example.com',      $rid('manager')],
            ['manager_sale', 'Phan Thanh Hoa',          'manager_sale@example.com', $rid('manager')],
            ['accountant',   'Trần Thị Bình',           'accountant@example.com',   $rid('accountant')],
            ['leader',       'Trương Quốc Bảo',         'leader@example.com',       $rid('leader')],
            ['employee',     'Phạm Thị Dung',           'employee@example.com',     $rid('employee')],
            ['emp_sale01',   'Nguyễn Thị Bích Ngọc',   'emp_sale01@example.com',   $rid('employee')],
            ['hoangvanem',   'Hoàng Văn Em',            'em@example.com',           $rid('employee')],
        ];

        foreach ($users as [$username, $name, $email, $roleId]) {
            DB::table('users')->insert([
                'username'   => $username,
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make('password'),
                'role_id'    => $roleId,
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedPositions(): void
    {
        $rows = [
            ['Giám đốc',      50000000, 5000000],
            ['Trưởng phòng',  25000000, 2000000],
            ['Phó phòng',     18000000, 1000000],
            ['Nhân viên',     12000000, 500000],
            ['Thực tập sinh',  4000000, 0],
        ];

        foreach ($rows as [$name, $base, $allowance]) {
            DB::table('positions')->insert([
                'position_name' => $name,
                'base_salary'   => $base,
                'allowance'     => $allowance,
                'description'   => $name,
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    private function seedDepartments(): void
    {
        $depts = [
            ['HR',   'Phòng Nhân sự',             'Quản lý nhân sự'],
            ['IT',   'Phòng Công nghệ Thông tin',  'Phát triển phần mềm'],
            ['ACC',  'Phòng Kế toán',              'Quản lý tài chính'],
            ['SALE', 'Phòng Kinh doanh',           'Kinh doanh & CSKH'],
            ['MKT',  'Phòng Marketing',            'Quảng bá thương hiệu'],
        ];

        foreach ($depts as [$code, $name, $desc]) {
            DB::table('departments')->insert([
                'department_code' => $code,
                'department_name' => $name,
                'description'     => $desc,
                'manager_id'      => null,
                'status'          => 'active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    private function seedShifts(): void
    {
        DB::table('shifts')->insert([
            ['shift_name' => 'Ca hành chính', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['shift_name' => 'Ca sáng',       'start_time' => '08:00:00', 'end_time' => '12:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['shift_name' => 'Ca chiều',      'start_time' => '13:00:00', 'end_time' => '17:00:00', 'created_at' => now(), 'updated_at' => now()],
            ['shift_name' => 'Ca test',       'start_time' => '21:15:00', 'end_time' => '21:17:00', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedContractTypes(): void
    {
        $types = [
            ['Thử việc 2 tháng',                2],
            ['Hợp đồng 6 tháng',                6],
            ['Hợp đồng 1 năm',                 12],
            ['Hợp đồng 2 năm',                 24],
            ['Hợp đồng không xác định thời hạn', 0],
            ['Hợp đồng thực tập',                3],
        ];

        foreach ($types as [$name, $months]) {
            DB::table('contract_types')->insert([
                'contract_name'  => $name,
                'duration_month' => $months,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // NHÂN SỰ
    // ═══════════════════════════════════════════════════════════════
    private function seedEmployees(): void
    {
        $dept = fn (string $code) => DB::table('departments')->where('department_code', $code)->value('id');
        $pos  = fn (string $name) => DB::table('positions')->where('position_name', $name)->value('id');

        // [code, dept, pos, name, gender, dob, phone, email, hire_date]
        $rows = [
            ['EMP001', $dept('HR'),   $pos('Giám đốc'),      'Nguyễn Văn An',          'male',   '1980-01-15', '0901000001', 'an@example.com',     '2023-01-01'],
            ['EMP002', $dept('IT'),   $pos('Trưởng phòng'),  'Lê Văn Thành',            'male',   '1988-05-20', '0901000002', 'thanh@example.com',  '2023-02-01'],
            ['EMP003', $dept('IT'),   $pos('Nhân viên'),     'Trương Quốc Bảo',         'male',   '1994-12-10', '0901000003', 'bao@example.com',    '2023-03-01'],
            ['EMP004', $dept('IT'),   $pos('Nhân viên'),     'Phạm Thị Dung',           'female', '1998-07-25', '0901000004', 'dung@example.com',   '2023-04-01'],
            ['EMP005', $dept('IT'),   $pos('Thực tập sinh'), 'Nguyễn Minh Khoa',        'male',   '2001-09-01', '0901000005', 'khoa@example.com',   '2026-01-15'],
            ['EMP006', $dept('SALE'), $pos('Trưởng phòng'),  'Phan Thanh Hoa',          'female', '1990-03-08', '0901000006', 'hoa@example.com',    '2023-01-15'],
            ['EMP007', $dept('SALE'), $pos('Nhân viên'),     'Nguyễn Thị Bích Ngọc',   'female', '1995-06-12', '0901000007', 'ngoc@example.com',   '2023-06-01'],
            ['EMP008', $dept('SALE'), $pos('Nhân viên'),     'Vũ Hoàng Long',           'male',   '1997-11-30', '0901000008', 'long@example.com',   '2024-01-01'],
            ['EMP009', $dept('ACC'),  $pos('Phó phòng'),     'Trần Thị Bình',           'female', '1992-04-17', '0901000009', 'binh@example.com',   '2023-01-01'],
            ['EMP010', $dept('MKT'),  $pos('Nhân viên'),     'Hoàng Văn Em',            'male',   '2000-08-22', '0901000010', 'em@example.com',     '2024-06-01'],
        ];

        foreach ($rows as [$code, $deptId, $posId, $name, $gender, $dob, $phone, $email, $hire]) {
            DB::table('employees')->insert([
                'employee_code' => $code,
                'department_id' => $deptId,
                'position_id'   => $posId,
                'full_name'     => $name,
                'gender'        => $gender,
                'date_of_birth' => $dob,
                'phone'         => $phone,
                'email'         => $email,
                'address'       => 'TP. Hồ Chí Minh',
                'hire_date'     => $hire,
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Gán manager_id cấp dưới
        $emp002 = DB::table('employees')->where('employee_code', 'EMP002')->value('id');
        $emp006 = DB::table('employees')->where('employee_code', 'EMP006')->value('id');

        DB::table('employees')->whereIn('employee_code', ['EMP003', 'EMP004', 'EMP005'])->update(['manager_id' => $emp002]);
        DB::table('employees')->whereIn('employee_code', ['EMP007', 'EMP008'])->update(['manager_id' => $emp006]);

        // Gán manager phòng ban
        DB::table('departments')->where('department_code', 'IT')->update(['manager_id' => $emp002]);
        DB::table('departments')->where('department_code', 'SALE')->update(['manager_id' => $emp006]);
    }

    private function linkUsersToEmployees(): void
    {
        $links = [
            'admin'        => 'EMP001',
            'manager'      => 'EMP002',
            'manager_sale' => 'EMP006',
            'accountant'   => 'EMP009',
            'leader'       => 'EMP003',
            'employee'     => 'EMP004',
            'emp_sale01'   => 'EMP007',
            'hoangvanem'   => 'EMP010',
        ];

        foreach ($links as $username => $code) {
            $userId = DB::table('users')->where('username', $username)->value('id');
            $email  = DB::table('users')->where('username', $username)->value('email');

            if ($userId) {
                DB::table('employees')->where('employee_code', $code)->update([
                    'user_id' => $userId,
                    'email'   => $email,
                ]);
            }
        }
    }

    private function seedContracts(): void
    {
        $typeId = fn (string $keyword) => DB::table('contract_types')
            ->where('contract_name', 'like', "%{$keyword}%")
            ->value('id');

        $type1y     = $typeId('1 năm');
        $type2y     = $typeId('2 năm');
        $typeUndef  = $typeId('không xác định');
        $typeIntern = $typeId('thực tập');
        $typeProb   = $typeId('Thử việc');

        // [empCode, salary, phone, fuel, meal, posAllowance, typeId, start, end]
        $rows = [
            ['EMP001', 50000000, 200000, 500000, 1500000, 5000000, $typeUndef,  '2023-01-01', null],
            ['EMP002', 25000000, 100000, 300000, 800000,  2000000, $type2y,     '2023-02-01', '2025-02-01'],
            ['EMP003', 15000000,  50000, 150000, 600000,   500000, $type1y,     '2025-01-01', '2026-01-01'],
            ['EMP004', 12000000,  50000, 100000, 500000,   300000, $type1y,     '2025-04-01', '2026-04-01'],
            ['EMP005',  4000000,      0,      0,      0,        0, $typeIntern, '2026-01-15', '2026-04-15'],
            ['EMP006', 25000000, 100000, 300000, 800000,  2000000, $type2y,     '2023-01-15', '2025-01-15'],
            ['EMP007', 12000000,  50000, 100000, 500000,   300000, $type1y,     '2023-06-01', '2024-06-01'],
            ['EMP008', 12000000,  50000, 100000, 500000,   300000, $typeProb,   '2024-01-01', '2024-03-01'],
            ['EMP009', 18000000,  50000, 200000, 700000,   800000, $type1y,     '2023-01-01', '2024-01-01'],
            ['EMP010', 10000000,  50000, 100000, 400000,   200000, $type1y,     '2024-06-01', '2025-06-01'],
        ];

        foreach ($rows as [$empCode, $salary, $phone, $fuel, $meal, $posAllow, $ctypeId, $start, $end]) {
            $emp = DB::table('employees')->where('employee_code', $empCode)->first();

            if (! $emp) {
                continue;
            }

            DB::table('contracts')->insert([
                'employee_id'        => $emp->id,
                'department_id'      => $emp->department_id,
                'position_id'        => $emp->position_id,
                'contract_type_id'   => $ctypeId,
                'contract_code'      => 'HD_' . $empCode,
                'start_date'         => $start,
                'end_date'           => $end,
                'salary'             => $salary,
                'allowance'          => 1500000,
                'allowance_meal'     => $meal,
                'allowance_phone'    => $phone,
                'allowance_fuel'     => $fuel,
                'allowance_position' => $posAllow,
                'status'             => 'active',
                'signed_date'        => $start,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    private function seedDocuments(): void
    {
        $emp = fn (string $code) => DB::table('employees')->where('employee_code', $code)->value('id');

        DB::table('employee_documents')->insert([
            ['employee_id' => $emp('EMP002'), 'document_type' => 'cccd', 'document_name' => 'CCCD Lê Văn Thành',       'file_path' => 'documents/emp002_cccd.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => $emp('EMP004'), 'document_type' => 'cv',   'document_name' => 'CV Phạm Thị Dung',         'file_path' => 'documents/emp004_cv.pdf',   'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => $emp('EMP006'), 'document_type' => 'cccd', 'document_name' => 'CCCD Phan Thanh Hoa',     'file_path' => 'documents/emp006_cccd.pdf', 'created_at' => now(), 'updated_at' => now()],
            ['employee_id' => $emp('EMP003'), 'document_type' => 'degree','document_name'=> 'Bằng Đại học EMP003',     'file_path' => 'documents/emp003_degree.pdf','created_at'=> now(), 'updated_at' => now()],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // CHẤM CÔNG (3 tháng gần nhất · 5 kịch bản xoay vòng)
    // ═══════════════════════════════════════════════════════════════
    private function seedAttendances(): void
    {
        $employees = Employee::query()->where('status', 'active')->orderBy('id')->get();

        $today = Carbon::today();
        $periods = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            $periods[] = [
                $month->copy()->startOfMonth()->format('Y-m-d'),
                $i === 0 ? $today->format('Y-m-d') : $month->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        foreach ($employees as $idx => $emp) {
            $scenario = $idx % 5;
            foreach ($periods as [$start, $end]) {
                $this->insertAttendanceForPeriod($emp->id, $start, $end, $scenario);
            }
        }
    }

    /** @return string[] */
    private function workingDays(string $start, string $end): array
    {
        $days = [];
        $cur  = Carbon::parse($start);
        $e    = Carbon::parse($end);

        while ($cur->lte($e)) {
            if (! $cur->isSunday()) {
                $days[] = $cur->format('Y-m-d');
            }
            $cur->addDay();
        }

        return $days;
    }

    private function insertAttendanceForPeriod(int $empId, string $start, string $end, int $scenario): void
    {
        $days  = $this->workingDays($start, $end);
        $total = count($days);

        if ($total === 0) {
            return;
        }

        switch ($scenario) {
            case 0: // Đi đủ công — đúng giờ
                foreach ($days as $date) {
                    $this->insertAttRow($empId, $date, 'present', '08:00:00', '17:00:00', 8.0, 0);
                }
                break;

            case 1: // 20 đi làm + còn lại vắng không phép
                $go = min(20, $total);
                for ($i = 0; $i < $go; $i++) {
                    $this->insertAttRow($empId, $days[$i], 'present', '08:00:00', '17:00:00', 8.0, 0);
                }
                for ($i = $go; $i < $total; $i++) {
                    $this->insertAttRow($empId, $days[$i], 'absent', null, null, 0.0, 0);
                }
                break;

            case 2: // Đủ công, 3 ngày đầu đi muộn 22 phút
                foreach ($days as $i => $date) {
                    $late = $i < 3;
                    $this->insertAttRow($empId, $date, $late ? 'late' : 'present', $late ? '08:22:00' : '08:00:00', '17:00:00', 8.0, $late ? 22 : 0);
                }
                break;

            case 3: // Đủ công — 2 ngày cuối nghỉ phép năm đã duyệt
                $go = max(0, $total - 2);
                for ($i = 0; $i < $go; $i++) {
                    $this->insertAttRow($empId, $days[$i], 'present', '08:00:00', '17:00:00', 8.0, 0);
                }
                foreach (array_slice($days, $go, 2) as $date) {
                    $this->insertAttRow($empId, $date, 'absent', null, null, 0.0, 0);
                    // Đơn nghỉ phép đã duyệt tương ứng (avoid dup với seedLeaveRequests)
                    DB::table('leave_requests')->insertOrIgnore([
                        'employee_id' => $empId,
                        'leave_type'  => 'annual',
                        'start_date'  => $date,
                        'end_date'    => $date,
                        'total_days'  => 1,
                        'reason'      => 'Nghỉ phép năm (auto seed)',
                        'status'      => 'approved',
                        'approved_by' => $this->adminUserId,
                        'approved_at' => now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
                break;

            default: // 22 đi làm + vắng nhiều không phép
                $go = min(22, $total);
                for ($i = 0; $i < $go; $i++) {
                    $this->insertAttRow($empId, $days[$i], 'present', '08:00:00', '17:00:00', 8.0, 0);
                }
                for ($i = $go; $i < $total; $i++) {
                    $this->insertAttRow($empId, $days[$i], 'absent', null, null, 0.0, 0);
                }
                break;
        }
    }

    private function insertAttRow(
        int $empId,
        string $date,
        string $status,
        ?string $checkIn,
        ?string $checkOut,
        float $hours,
        int $lateMin
    ): void {
        DB::table('attendances')->insert([
            'employee_id'     => $empId,
            'shift_id'        => $this->defaultShiftId,
            'attendance_date' => $date,
            'check_in'        => $checkIn ? "{$date} {$checkIn}" : null,
            'check_out'       => $checkOut ? "{$date} {$checkOut}" : null,
            'work_hours'      => $hours,
            'late_minutes'    => $lateMin,
            'status'          => $status,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // NGHỈ PHÉP (đa dạng trạng thái)
    // ═══════════════════════════════════════════════════════════════
    private function seedLeaveRequests(): void
    {
        $emp = fn (string $code) => DB::table('employees')->where('employee_code', $code)->value('id');
        $today = Carbon::today();

        // [empId, type, start, end, days, reason, status, approvedBy, rejectReason]
        $today = Carbon::today();
        $leaves = [
            [$emp('EMP003'), 'annual',  $today->copy()->addDays(3)->toDateString(), $today->copy()->addDays(5)->toDateString(), 3, 'Du lịch gia đình',           'pending',  null,               null],
            [$emp('EMP004'), 'sick',    $today->copy()->subDays(10)->toDateString(), $today->copy()->subDays(9)->toDateString(), 2, 'Bị sốt cao',                 'approved', $this->adminUserId, null],
            [$emp('EMP007'), 'annual',  $today->copy()->subDays(5)->toDateString(), $today->copy()->subDays(5)->toDateString(), 1, 'Việc cá nhân',               'approved', $this->adminUserId, null],
            [$emp('EMP008'), 'unpaid',  $today->copy()->addDays(10)->toDateString(), $today->copy()->addDays(15)->toDateString(), 6, 'Việc gia đình đột xuất',     'rejected', null,               'Thiếu nhân sự'],
            [$emp('EMP010'), 'annual',  $today->copy()->addDays(2)->toDateString(), $today->copy()->addDays(3)->toDateString(), 2, 'Nghỉ phép năm còn tồn',     'pending',  null,               null],
            [$emp('EMP003'), 'sick',    $today->copy()->subMonth()->addDays(4)->toDateString(), $today->copy()->subMonth()->addDays(5)->toDateString(), 2, 'Ốm (tháng trước đã duyệt)', 'approved', $this->adminUserId, null],
            [$emp('EMP004'), 'annual',  $today->copy()->addMonth()->addDays(10)->toDateString(), $today->copy()->addMonth()->addDays(12)->toDateString(), 3, 'Xin nghỉ tháng tới', 'pending', null, null],
        ];

        foreach ($leaves as [$empId, $type, $start, $end, $days, $reason, $status, $approvedBy, $rejectReason]) {
            if (! $empId) {
                continue;
            }

            DB::table('leave_requests')->insert([
                'employee_id'   => $empId,
                'leave_type'    => $type,
                'start_date'    => $start,
                'end_date'      => $end,
                'total_days'    => $days,
                'reason'        => $reason,
                'status'        => $status,
                'approved_by'   => $approvedBy,
                'approved_at'   => $approvedBy ? now() : null,
                'reject_reason' => $rejectReason,
                'rejected_by'   => $rejectReason ? $this->adminUserId : null,
                'rejected_at'   => $rejectReason ? now() : null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // TĂNG CA
    // ═══════════════════════════════════════════════════════════════
    private function seedOvertimeRequests(): void
    {
        $emp = fn (string $code) => DB::table('employees')->where('employee_code', $code)->value('id');
        $today = Carbon::today();

        // [empId, work_date, start, end, hours, reason, status]
        $rows = [
            [$emp('EMP002'), $today->copy()->subDays(10)->toDateString(), '18:00', '21:00', 3.0,  'Triển khai hệ thống mới',   'completed'],
            [$emp('EMP003'), $today->copy()->subDays(7)->toDateString(), '18:00', '20:00', 2.0,  'Fix bug production',        'approved'],
            [$emp('EMP004'), $today->copy()->subDays(3)->toDateString(), '18:00', '19:30', 1.5,  'Hỗ trợ QA test release',   'pending'],
            [$emp('EMP006'), $today->copy()->subDays(12)->toDateString(), '18:00', '21:00', 3.0,  'Chốt báo cáo doanh số', 'completed'],
            [$emp('EMP007'), $today->copy()->addDays(2)->toDateString(), '18:00', '20:30', 2.5,  'Ký hợp đồng khách hàng',   'approved'],
            [$emp('EMP003'), $today->copy()->subMonth()->addDays(15)->toDateString(), '18:00', '21:30', 3.5,  'Tăng ca sprint tháng trước', 'completed'],
        ];

        foreach ($rows as [$empId, $workDate, $start, $end, $hours, $reason, $status]) {
            if (! $empId) {
                continue;
            }

            DB::table('overtime_requests')->insert([
                'employee_id' => $empId,
                'work_date'   => $workDate,
                'start_time'  => $start,
                'end_time'    => $end,
                'total_hours' => $hours,
                'reason'      => $reason,
                'status'      => $status,
                'approved_by' => in_array($status, ['approved', 'completed']) ? $this->adminUserId : null,
                'approved_at' => in_array($status, ['approved', 'completed']) ? now() : null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // KPI
    // ═══════════════════════════════════════════════════════════════
    private function seedKPIs(): void
    {
        $dept = fn (string $code) => DB::table('departments')->where('department_code', $code)->value('id');

        $kpis = [
            [
                'code'          => 'KPI0001',
                'title'         => 'Doanh số tháng',
                'description'   => 'Đạt doanh thu mục tiêu trong tháng',
                'target'        => '300',
                'unit'          => 'Triệu đồng',
                'weight'        => 50,
                'max_score'     => 100,
                'period'        => 'month',
                'positions'     => ['manager'],
                'department_id' => $dept('SALE'),
                'start_date'    => '2026-07-01',
                'end_date'      => '2026-07-31',
                'status'        => 'active',
                'tasks' => [
                    ['Gọi điện cho ≥30 khách hàng tiềm năng/tuần',  'Ghi kết quả vào CRM'],
                    ['Lên kế hoạch chăm sóc khách hàng cũ',          null],
                    ['Tổng hợp báo cáo doanh số cuối tháng',         'Gửi file Excel cho trưởng phòng'],
                ],
            ],
            [
                'code'          => 'KPI0002',
                'title'         => 'Chấm công đầy đủ',
                'description'   => 'Đi làm đúng giờ và đủ công trong kỳ',
                'target'        => '95',
                'unit'          => '%',
                'weight'        => 20,
                'max_score'     => 100,
                'period'        => 'month',
                'positions'     => ['manager'],
                'department_id' => $dept('IT'),
                'start_date'    => '2026-07-01',
                'end_date'      => '2026-07-31',
                'status'        => 'active',
                'tasks' => [
                    ['Check-in trước 08:10 mỗi ngày làm việc', null],
                    ['Gửi đơn nghỉ phép ≥1 ngày trước qua HRM', null],
                ],
            ],
            [
                'code'          => 'KPI0003',
                'title'         => 'Hoàn thành task phát triển',
                'description'   => 'Số task hoàn thành đúng deadline trong quý',
                'target'        => '30',
                'unit'          => 'Task',
                'weight'        => 30,
                'max_score'     => 100,
                'period'        => 'quarter',
                'positions'     => ['manager'],
                'department_id' => $dept('IT'),
                'start_date'    => '2026-07-01',
                'end_date'      => '2026-09-30',
                'status'        => 'active',
                'tasks' => [
                    ['Viết unit test cho module mới (coverage ≥ 70%)', 'Sử dụng PHPUnit'],
                    ['Review code ≥2 PR/tuần',                          null],
                    ['Cập nhật tài liệu kỹ thuật sau mỗi sprint',       null],
                ],
            ],
            [
                'code'          => 'KPI0004',
                'title'         => 'Độ hài lòng khách hàng',
                'description'   => 'Điểm NPS từ khảo sát sau dịch vụ',
                'target'        => '80',
                'unit'          => '%',
                'weight'        => 40,
                'max_score'     => 100,
                'period'        => 'quarter',
                'positions'     => ['manager'],
                'department_id' => $dept('SALE'),
                'start_date'    => '2026-07-01',
                'end_date'      => '2026-09-30',
                'status'        => 'active',
                'tasks' => [
                    ['Gửi form khảo sát sau mỗi hợp đồng ký', null],
                    ['Xử lý khiếu nại trong vòng 24 giờ',      null],
                ],
            ],
        ];

        foreach ($kpis as $k) {
            $tasks = $k['tasks'];
            $deptId = $k['department_id'];
            unset($k['tasks'], $k['department_id']);

            $kpiId = DB::table('kpis')->insertGetId(array_merge($k, [
                'positions'  => json_encode($k['positions']),
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            DB::table('kpi_department')->insert(['kpi_id' => $kpiId, 'department_id' => $deptId]);

            foreach ($tasks as $order => [$title, $description]) {
                DB::table('kpi_tasks')->insert([
                    'kpi_id'      => $kpiId,
                    'title'       => $title,
                    'description' => $description,
                    'sort_order'  => $order,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    private function seedKPIAssignments(): void
    {
        $kpi  = fn (string $code) => DB::table('kpis')->where('code', $code)->first();
        $user = fn (string $u) => DB::table('users')->where('username', $u)->value('id');

        // [kpi_code, manager_username, status]
        $assignments = [
            ['KPI0001', 'manager_sale', 'active'],
            ['KPI0002', 'manager',      'active'],
            ['KPI0003', 'manager',      'pending'],
            ['KPI0004', 'manager_sale', 'pending'],
        ];

        foreach ($assignments as [$kpiCode, $managerUsername, $status]) {
            $kpiRow    = $kpi($kpiCode);
            $mgrUserId = $user($managerUsername);

            if (! $kpiRow || ! $mgrUserId) {
                continue;
            }

            DB::table('kpi_assignments')->insert([
                'kpi_id'      => $kpiRow->id,
                'manager_id'  => $mgrUserId,
                'target'      => is_numeric($kpiRow->target) ? (float) $kpiRow->target : 0,
                'start_date'  => $kpiRow->start_date,
                'end_date'    => $kpiRow->end_date,
                'status'      => $status,
                'assigned_by' => $this->adminUserId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function seedEmployeeKPIs(): void
    {
        $emp = fn (string $code) => DB::table('employees')->where('employee_code', $code)->value('id');
        $kpi = fn (string $code) => DB::table('kpis')->where('code', $code)->value('id');
        $assignId = fn (string $kpiCode) => DB::table('kpi_assignments')
            ->join('kpis', 'kpis.id', '=', 'kpi_assignments.kpi_id')
            ->where('kpis.code', $kpiCode)
            ->value('kpi_assignments.id');

        // [kpi_code, emp_code, target, progress, status, score, comment]
        $rows = [
            ['KPI0002', 'EMP003', 'Chấm công ≥95%',  100, 'completed',   92,   'Hoàn thành xuất sắc'],
            ['KPI0002', 'EMP004', 'Chấm công ≥95%',   80, 'in_progress', null, 'Cần cải thiện đúng giờ'],
            ['KPI0001', 'EMP007', 'Doanh số 150 tr',  60, 'in_progress', null, 'Đang đạt ~90 triệu'],
            ['KPI0001', 'EMP008', 'Doanh số 100 tr',  40, 'pending',     null, null],
        ];

        foreach ($rows as [$kpiCode, $empCode, $target, $progress, $status, $score, $comment]) {
            $aId    = $assignId($kpiCode);
            $empId  = $emp($empCode);
            $kpiId  = $kpi($kpiCode);

            if (! $aId || ! $empId) {
                continue;
            }

            DB::table('employee_kpis')->insert([
                'assignment_id' => $aId,
                'employee_id'   => $empId,
                'kpi_id'        => $kpiId,
                'target'        => $target,
                'progress'      => $progress,
                'status'        => $status,
                'score'         => $score,
                'comment'       => $comment,
                'deadline'      => '2026-07-31',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // BẢNG LƯƠNG (4 kỳ đa trạng thái)
    // ═══════════════════════════════════════════════════════════════
    private function seedPayrollPeriods(): void
    {
        $today = Carbon::today();
        $periods = [
            [$today->copy()->subMonths(2)->month, $today->copy()->subMonths(2)->year, 'paid'],
            [$today->copy()->subMonth()->month, $today->copy()->subMonth()->year, 'paid'],
            [$today->month, $today->year, 'calculated'],
            [$today->copy()->addMonth()->month, $today->copy()->addMonth()->year, 'open'],
        ];

        foreach ($periods as [$month, $year, $status]) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $label = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
            $approvedAt = $status === 'paid' ? $end->copy()->subDays(5)->format('Y-m-d') : null;
            $paidAt = $status === 'paid' ? $end->copy()->subDays(2)->format('Y-m-d') : null;

            DB::table('payroll_periods')->insert([
                'name'        => "Kỳ lương tháng {$label}/{$year}",
                'month'       => $month,
                'year'        => $year,
                'start_date'  => $start->format('Y-m-d'),
                'end_date'    => $end->format('Y-m-d'),
                'status'      => $status,
                'approved_by' => $approvedAt ? $this->adminUserId : null,
                'approved_at' => $approvedAt,
                'paid_by'     => $paidAt ? $this->adminUserId : null,
                'paid_at'     => $paidAt,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function calculatePayrolls(): void
    {
        /** @var PayrollService $svc */
        $svc = app(PayrollService::class);

        foreach (PayrollPeriod::query()->whereIn('status', ['open', 'calculated', 'paid'])->get() as $period) {
            $result = $svc->calculatePayrollForPeriod($period);

            if ($result !== 'success') {
                $this->command?->warn("  Kỳ lương #{$period->id} ({$period->name}): {$result}");
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // TUYỂN DỤNG
    // ═══════════════════════════════════════════════════════════════
    private function seedRecruitment(): void
    {
        $dept = fn (string $code) => DB::table('departments')->where('department_code', $code)->value('id');

        // Job posts (chỉ dùng cột thực tế: department_id, title, quantity, description, status + recruiter/salary/location/type/deadline/requirements/benefits)
        $jobs = [
            [$dept('IT'),   'Lập trình viên PHP',   3, 'open',   '2026-08-31', 15000000, 25000000, 'Yêu cầu ≥2 năm Laravel', 'Bảo hiểm, thưởng KPI'],
            [$dept('HR'),   'Nhân viên HR',          2, 'open',   '2026-08-15', 10000000, 18000000, 'Tốt nghiệp ĐH ngành HR', 'Bảo hiểm đầy đủ'],
            [$dept('ACC'),  'Kế toán viên',          1, 'closed', '2026-05-31', 12000000, 20000000, 'CPA là lợi thế',         null],
            [$dept('SALE'), 'Nhân viên Kinh doanh',  2, 'open',   '2026-09-30', 10000000, 20000000, 'Có kỹ năng thuyết phục', 'Hoa hồng hấp dẫn'],
        ];

        foreach ($jobs as [$deptId, $title, $qty, $status, $deadline, $salMin, $salMax, $req, $benefits]) {
            DB::table('job_posts')->insert([
                'department_id'        => $deptId,
                'title'                => $title,
                'quantity'             => $qty,
                'description'          => "Mô tả vị trí: {$title}",
                'status'               => $status,
                'salary_min'           => $salMin,
                'salary_max'           => $salMax,
                'work_location'        => 'TP. Hồ Chí Minh',
                'work_type'            => 'full_time',
                'application_deadline' => $deadline,
                'requirements'         => $req,
                'benefits'             => $benefits,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // Candidates
        $jp = fn (string $keyword) => DB::table('job_posts')->where('title', 'like', "%{$keyword}%")->value('id');

        $candidates = [
            [$jp('PHP'),      'Nguyễn Văn Alpha',     '0901111001', 'alpha@gmail.com', '2001-03-10', 'new'],
            [$jp('PHP'),      'Trần Thị Beta',         '0901111002', 'beta@gmail.com',  '2000-07-15', 'interview'],
            [$jp('PHP'),      'Lê Quang Gamma',        '0901111003', 'gamma@gmail.com', '2002-01-20', 'passed'],
            [$jp('HR'),       'Phạm Thị Delta',        '0901111004', 'delta@gmail.com', '1999-09-05', 'new'],
            [$jp('Kinh doanh'),'Hoàng Văn Epsilon',   '0901111005', 'eps@gmail.com',   '1998-11-25', 'interview'],
            [$jp('Kinh doanh'),'Võ Thị Zeta',         '0901111006', 'zeta@gmail.com',  '2000-04-30', 'failed'],
        ];

        foreach ($candidates as [$jpId, $name, $phone, $email, $dob, $status]) {
            if (! $jpId) {
                continue;
            }

            DB::table('candidates')->insert([
                'job_post_id' => $jpId,
                'full_name'   => $name,
                'phone'       => $phone,
                'email'       => $email,
                'birth_date'  => $dob,
                'address'     => 'Hà Nội',
                'cv_file'     => null,
                'status'      => $status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Interviews — chỉ cho candidate đang ở bước "interview"
        // interviewer_id tham chiếu employees.id (không phải users.id)
        $interviewerEmpId = DB::table('employees')->where('employee_code', 'EMP001')->value('id');

        $interviewCandidates = DB::table('candidates')->where('status', 'interview')->get();

        foreach ($interviewCandidates as $cand) {
            DB::table('interviews')->insert([
                'candidate_id'   => $cand->id,
                'interviewer_id' => $interviewerEmpId,
                'interview_date' => Carbon::now()->addDays(5)->setTime(9, 0)->toDateTimeString(),
                'result'         => 'pending',
                'status'         => 'scheduled',
                'note'           => 'Phỏng vấn vòng 1 kỹ thuật',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // THÔNG BÁO (chỉ dùng type trong enum: system / leave / payroll / kpi)
    // ═══════════════════════════════════════════════════════════════
    private function seedNotifications(): void
    {
        $notifications = [
            ['Lương tháng 7/2026 đã được tính',  'payroll', 'Bảng lương tháng 7 đã sẵn sàng. Vui lòng kiểm tra và duyệt.'],
            ['Đơn nghỉ phép mới chờ duyệt',      'leave',   'Nhân viên Trương Quốc Bảo vừa gửi đơn nghỉ phép 3 ngày.'],
            ['KPI mới được giao cho Manager',    'kpi',     'KPI "Chấm công đầy đủ" đã giao cho Manager phòng IT.'],
            ['KPI tháng 7 chờ phản hồi',          'kpi',     'Manager phòng Kinh doanh có 1 KPI mới đang ở trạng thái pending.'],
            ['Thông báo hệ thống',               'system',  'Hệ thống HRM đã được nâng cấp. Một số tính năng mới đã có sẵn.'],
        ];

        $allUserIds = DB::table('users')->pluck('id')->all();

        foreach ($notifications as [$title, $type, $content]) {
            $notifId = DB::table('notifications')->insertGetId([
                'title'           => $title,
                'type'            => $type,
                'content'         => $content,
                'sender_id'       => $this->adminUserId,
                'delivery_status' => 'sent',
                'sent_at'         => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            foreach ($allUserIds as $uid) {
                DB::table('notification_users')->insert([
                    'notification_id' => $notifId,
                    'user_id'         => $uid,
                    'is_read'         => false,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // PHÂN CA LÀM VIỆC
    // ═══════════════════════════════════════════════════════════════
    private function seedEmployeeShifts(): void
    {
        $employees = Employee::query()->where('status', 'active')->orderBy('id')->get();
        $adminShiftId = (int) DB::table('shifts')->where('shift_name', 'Ca hành chính')->value('id');
        $morningShiftId = (int) DB::table('shifts')->where('shift_name', 'Ca sáng')->value('id');
        $testShiftId = (int) DB::table('shifts')->where('shift_name', 'Ca test')->value('id');
        $today = Carbon::today();
        $start = $today->copy()->startOfMonth();
        $end = $today->copy()->endOfMonth();

        foreach ($employees as $employee) {
            $cur = $start->copy();
            while ($cur->lte($end)) {
                if ($cur->isSunday()) {
                    $cur->addDay();
                    continue;
                }

                DB::table('employee_shifts')->insert([
                    'employee_id' => $employee->id,
                    'shift_id'    => $adminShiftId,
                    'work_date'   => $cur->format('Y-m-d'),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                if ($employee->employee_code === 'EMP010' && $cur->isSameDay($today)) {
                    DB::table('employee_shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_id'    => $morningShiftId,
                        'work_date'   => $cur->format('Y-m-d'),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    DB::table('employee_shifts')->insert([
                        'employee_id' => $employee->id,
                        'shift_id'    => $testShiftId,
                        'work_date'   => $cur->format('Y-m-d'),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                $cur->addDay();
            }
        }
    }

    private function seedTodayAttendance(): void
    {
        $today = Carbon::today()->format('Y-m-d');
        $emp010Id = (int) DB::table('employees')->where('employee_code', 'EMP010')->value('id');
        $shiftId = (int) DB::table('shifts')->where('shift_name', 'Ca sáng')->value('id');

        if (! $emp010Id || ! $shiftId) {
            return;
        }

        DB::table('attendances')->updateOrInsert(
            ['employee_id' => $emp010Id, 'attendance_date' => $today],
            [
                'shift_id'           => $shiftId,
                'check_in'           => "{$today} 08:41:45",
                'check_out'          => null,
                'work_hours'         => 0,
                'late_minutes'       => 36,
                'status'             => 'late',
                'check_in_method'    => 'face',
                'recognition_confidence' => 0.92,
                'liveness_score'     => 0.88,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );

        $samples = [
            ['EMP002', '08:05:00', '17:02:00', 'present', 0, 'face'],
            ['EMP003', '08:00:00', null, 'present', 0, 'face'],
            ['EMP004', '08:18:00', null, 'late', 13, 'manual'],
            ['EMP006', '07:55:00', '17:10:00', 'present', 0, 'face'],
            ['EMP007', null, null, 'absent', 0, null],
        ];

        foreach ($samples as [$code, $checkIn, $checkOut, $status, $late, $method]) {
            $empId = (int) DB::table('employees')->where('employee_code', $code)->value('id');
            if (! $empId) {
                continue;
            }

            $payload = [
                'shift_id'         => $this->defaultShiftId,
                'check_in'         => $checkIn ? "{$today} {$checkIn}" : null,
                'check_out'        => $checkOut ? "{$today} {$checkOut}" : null,
                'work_hours'       => $checkOut ? 8.0 : 0,
                'late_minutes'     => $late,
                'status'           => $status,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if ($method) {
                $payload['check_in_method'] = $method;
            }
            if ($checkOut && $method) {
                $payload['check_out_method'] = $method;
            }

            DB::table('attendances')->updateOrInsert(
                ['employee_id' => $empId, 'attendance_date' => $today],
                $payload
            );
        }
    }

    private function seedHolidays(): void
    {
        $year = now()->year;
        $rows = [
            ['Tết Dương lịch', "{$year}-01-01", "{$year}-01-01", 'public_holiday', 'Nghỉ Tết Dương lịch'],
            ['Giỗ Tổ Hùng Vương', "{$year}-04-18", "{$year}-04-18", 'public_holiday', 'Nghỉ lễ 10/3'],
            ['Quốc tế Lao động', "{$year}-05-01", "{$year}-05-01", 'public_holiday', 'Nghỉ 1/5'],
            ['Quốc khánh', "{$year}-09-02", "{$year}-09-02", 'public_holiday', 'Nghỉ 2/9'],
            ['Team building công ty', now()->addMonth()->startOfMonth()->format('Y-m-d'), now()->addMonth()->startOfMonth()->addDays(1)->format('Y-m-d'), 'company_trip', 'Du lịch team building'],
        ];

        foreach ($rows as [$name, $start, $end, $type, $desc]) {
            DB::table('holidays')->insert([
                'name'        => $name,
                'start_date'  => $start,
                'end_date'    => $end,
                'type'        => $type,
                'description' => $desc,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function seedSalaryAdvances(): void
    {
        $emp = fn (string $code) => (int) DB::table('employees')->where('employee_code', $code)->value('id');
        $rows = [
            ['TU-' . now()->format('Ym') . '-001', 'EMP004', 5_000_000, 'pending',  'Ứng lương giữa tháng'],
            ['TU-' . now()->format('Ym') . '-002', 'EMP007', 3_000_000, 'approved', 'Chi phí đi lại công tác'],
            ['TU-' . now()->subMonth()->format('Ym') . '-001', 'EMP003', 8_000_000, 'partial', 'Ứng lương tháng trước'],
            ['TU-' . now()->subMonth()->format('Ym') . '-002', 'EMP008', 2_000_000, 'rejected', 'Lý do không hợp lệ'],
        ];

        foreach ($rows as [$code, $empCode, $amount, $status, $reason]) {
            $empId = $emp($empCode);
            if (! $empId) {
                continue;
            }

            DB::table('salary_advances')->insert([
                'advance_code'    => $code,
                'employee_id'     => $empId,
                'amount'          => $amount,
                'amount_settled'  => $status === 'partial' ? 4_000_000 : 0,
                'request_date'    => now()->toDateString(),
                'reason'          => $reason,
                'status'          => $status,
                'requested_by'    => $empId ? DB::table('employees')->where('id', $empId)->value('user_id') : null,
                'approved_by'     => in_array($status, ['approved', 'partial']) ? $this->adminUserId : null,
                'approved_at'     => in_array($status, ['approved', 'partial']) ? now() : null,
                'rejected_by'     => $status === 'rejected' ? $this->adminUserId : null,
                'rejected_at'     => $status === 'rejected' ? now() : null,
                'rejection_reason'=> $status === 'rejected' ? 'Chưa đủ điều kiện ứng lương' : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    private function seedEarlyLeaveRequests(): void
    {
        $emp = fn (string $code) => (int) DB::table('employees')->where('employee_code', $code)->value('id');
        $rows = [
            ['EMP004', today()->toDateString(), '16:00:00', 'Đưa con đi khám bệnh', 'pending'],
            ['EMP007', today()->subDays(2)->toDateString(), '15:30:00', 'Việc gia đình', 'approved'],
            ['EMP010', today()->addDays(3)->toDateString(), '11:30:00', 'Họp trường cho con', 'pending'],
            ['EMP008', today()->subDays(5)->toDateString(), '16:30:00', 'Không có lý do chính đáng', 'rejected'],
        ];

        foreach ($rows as [$empCode, $date, $time, $reason, $status]) {
            $empId = $emp($empCode);
            if (! $empId) {
                continue;
            }

            DB::table('early_leave_requests')->insert([
                'employee_id'   => $empId,
                'request_date'  => $date,
                'leave_time'    => $time,
                'reason'        => $reason,
                'status'        => $status,
                'approved_by'   => $status === 'approved' ? $this->adminUserId : null,
                'approved_at'   => $status === 'approved' ? now() : null,
                'rejected_by'   => $status === 'rejected' ? $this->adminUserId : null,
                'rejected_at'   => $status === 'rejected' ? now() : null,
                'reject_reason' => $status === 'rejected' ? 'Không đủ nhân sự thay thế' : null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    private function seedInsurances(): void
    {
        $employees = Employee::query()->where('status', 'active')->whereIn('employee_code', ['EMP002', 'EMP003', 'EMP004', 'EMP006', 'EMP007', 'EMP009'])->get();

        foreach ($employees as $employee) {
            $salary = (float) (DB::table('contracts')->where('employee_id', $employee->id)->value('salary') ?? 10_000_000);

            DB::table('employee_insurances')->insert([
                'employee_id'          => $employee->id,
                'social_insurance_number' => 'BH' . str_pad((string) $employee->id, 8, '0', STR_PAD_LEFT),
                'health_insurance_code'   => 'YT' . str_pad((string) $employee->id, 8, '0', STR_PAD_LEFT),
                'contribution_salary'  => min($salary, 36_000_000),
                'start_date'           => $employee->hire_date,
                'status'               => 'active',
                'managed_by'           => $this->adminUserId,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    private function seedTaxProfiles(): void
    {
        $samples = [
            ['EMP002', '8012345678', [['Vợ', 'Nguyễn Thị Lan', 'spouse']]],
            ['EMP004', '8023456789', [['Con', 'Phạm Minh An', 'child']]],
            ['EMP006', '8034567890', []],
            ['EMP009', '8045678901', [['Con', 'Trần Bảo Ngọc', 'child'], ['Cha', 'Trần Văn Hùng', 'parent']]],
        ];

        foreach ($samples as [$empCode, $taxCode, $dependents]) {
            $empId = (int) DB::table('employees')->where('employee_code', $empCode)->value('id');
            if (! $empId) {
                continue;
            }

            DB::table('employee_tax_profiles')->insert([
                'employee_id'        => $empId,
                'tax_code'           => $taxCode,
                'personal_deduction' => 11_000_000,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            foreach ($dependents as [$relationship, $name, $relCode]) {
                DB::table('tax_dependents')->insert([
                    'employee_id'        => $empId,
                    'full_name'          => $name,
                    'relationship'       => $relCode,
                    'date_of_birth'      => '2015-05-10',
                    'monthly_deduction'  => 4_400_000,
                    'start_date'         => '2024-01-01',
                    'is_active'          => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    private function seedFaceDescriptors(): void
    {
        $codes = ['EMP002', 'EMP003', 'EMP004', 'EMP006', 'EMP007', 'EMP010'];

        foreach ($codes as $code) {
            $empId = (int) DB::table('employees')->where('employee_code', $code)->value('id');
            if (! $empId) {
                continue;
            }

            $embedding = [];
            for ($i = 0; $i < 512; $i++) {
                $embedding[] = round(sin($empId * 0.1 + $i * 0.01) * 0.5, 6);
            }

            DB::table('employee_face_descriptors')->insert([
                'employee_id' => $empId,
                'embedding'   => json_encode($embedding),
                'quality'     => 0.85,
                'model_name'  => 'buffalo_l',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SUMMARY TABLE
    // ═══════════════════════════════════════════════════════════════
    private function printSummary(): void
    {
        $this->command?->newLine();
        $this->command?->info('✅  MasterDemoSeeder hoàn tất!');
        $this->command?->newLine();

        $this->command?->table(
            ['Vai trò', 'Username', 'Password', 'Ghi chú'],
            [
                ['Admin',     'admin',        'password', 'Toàn quyền — /admin/*'],
                ['Manager',   'manager',      'password', 'Phòng IT — /manager/*'],
                ['Manager',   'manager_sale', 'password', 'Phòng Kinh doanh'],
                ['Kế toán',   'accountant',   'password', 'Bảng lương, BH, thuế — /accountant/*'],
                ['Trưởng nhóm','leader',      'password', 'Nhóm IT — /leader/*'],
                ['NV',        'employee',     'password', 'Phạm Thị Dung — EMP004'],
                ['NV',        'emp_sale01',   'password', 'Nguyễn Thị Bích Ngọc — EMP007'],
                ['NV',        'hoangvanem',   'password', 'Hoàng Văn Em — EMP010 · test chấm công'],
            ]
        );

        $this->command?->table(
            ['Kỳ lương', 'Trạng thái', 'Gợi ý test'],
            [
                ['05/2026', 'paid',       'Xem phiếu lương đã chi trả'],
                ['06/2026', 'paid',       'Xem phiếu lương đã chi trả'],
                ['07/2026', 'calculated', 'Bấm Duyệt → Chi trả → Đóng kỳ'],
                ['08/2026', 'open',       'Bấm "Tính lương" để chạy tự động'],
            ]
        );

        $this->command?->table(
            ['Chức năng', 'Dữ liệu có sẵn'],
            [
                ['Nhân sự',      '10 nhân viên · 5 phòng ban · hợp đồng đầy đủ'],
                ['Phân ca',      'Ca hành chính cả tháng · EMP010 có ca sáng + ca test hôm nay'],
                ['Chấm công',    '3 tháng gần nhất + dữ liệu hôm nay (vào/ra/muộn/vắng)'],
                ['Khuôn mặt',    '6 nhân viên đã đăng ký embedding (admin UI)'],
                ['Nghỉ phép',    '7 đơn: pending / approved / rejected'],
                ['Về sớm',       '4 đơn xin về sớm đa trạng thái'],
                ['Tăng ca',      '6 đơn: pending / approved / completed'],
                ['Ứng lương',    '4 đơn: pending / approved / partial / rejected'],
                ['KPI',          '4 KPI + nhiệm vụ + giao manager + giao nhân viên'],
                ['Lương',        '4 kỳ đa trạng thái, tính tự động từ chấm công'],
                ['Bảo hiểm',     '6 nhân viên có hồ sơ BHXH/BHYT'],
                ['Thuế',         '4 hồ sơ thuế + người phụ thuộc'],
                ['Ngày lễ',      '5 ngày lễ / sự kiện công ty'],
                ['Tuyển dụng',   '4 tin tuyển · 6 ứng viên · 2 lịch phỏng vấn'],
                ['Thông báo',    '5 thông báo gửi đến toàn bộ user'],
            ]
        );
    }
}
