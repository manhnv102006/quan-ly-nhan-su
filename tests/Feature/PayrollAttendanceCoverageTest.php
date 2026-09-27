<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use App\Services\PayrollService;

beforeEach(function () {
    $this->shift = Shift::create([
        'shift_name' => 'Ca hành chính test',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'CNTT001',
        'full_name' => 'Lê Văn Thành',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '0900000001',
        'email' => 'cntt001@example.com',
        'hire_date' => '2020-01-01',
        'status' => 'active',
    ]);

    $this->period = PayrollPeriod::create([
        'name' => 'Kỳ lương tháng 10/2026',
        'month' => 10,
        'year' => 2026,
        'start_date' => '2026-10-30',
        'end_date' => '2026-10-31',
        'status' => 'open',
        'is_active' => true,
    ]);
});

function markAttendance(Employee $employee, Shift $shift, string $date, string $status): void
{
    Attendance::create([
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'attendance_date' => $date,
        'status' => $status,
    ]);
}

test('missing attendance day blocks payroll calculation and names the date', function () {
    markAttendance($this->employee, $this->shift, '2026-10-30', 'present');

    $service = app(PayrollService::class);
    $gaps = $service->attendanceCoverageGaps($this->period);

    expect($gaps)->toHaveCount(1)
        ->and($gaps[0]['missing_count'])->toBe(1)
        ->and($gaps[0]['missing_dates'])->toBe(['2026-10-31']);

    $message = $service->attendanceCoverageMessage($gaps, 'tính lương');
    expect($message)->toContain('CNTT001 Lê Văn Thành')
        ->and($message)->toContain('còn 1 ngày chưa có dữ liệu chấm công')
        ->and($message)->toContain('31/10/2026')
        ->and($message)->toContain('cần xử lý trước khi tính lương');

    expect($service->calculatePayrollForPeriod($this->period))->toBe('incomplete_attendance');
    expect(Payroll::query()->count())->toBe(0);
});

test('present late absent and leave cover standard days and sunday is not required', function () {
    $this->period->update([
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-11',
    ]);

    markAttendance($this->employee, $this->shift, '2026-10-05', 'present');
    markAttendance($this->employee, $this->shift, '2026-10-06', 'late');
    markAttendance($this->employee, $this->shift, '2026-10-07', 'absent');
    markAttendance($this->employee, $this->shift, '2026-10-08', 'leave');
    markAttendance($this->employee, $this->shift, '2026-10-09', 'present');
    markAttendance($this->employee, $this->shift, '2026-10-10', 'present');

    expect(app(PayrollService::class)->attendanceCoverageGaps($this->period))->toBe([]);
});

test('a weekday holiday counts as coverage without an attendance row', function () {
    Holiday::create([
        'name' => 'Nghỉ bù test',
        'start_date' => '2026-10-31',
        'end_date' => '2026-10-31',
        'type' => 'public_holiday',
    ]);

    markAttendance($this->employee, $this->shift, '2026-10-30', 'present');

    expect(app(PayrollService::class)->attendanceCoverageGaps($this->period))->toBe([]);
});

test('recalculate keeps existing payroll when attendance is still incomplete', function () {
    markAttendance($this->employee, $this->shift, '2026-10-30', 'present');

    $this->period->update(['status' => 'calculated']);

    Payroll::create([
        'employee_id' => $this->employee->id,
        'payroll_period_id' => $this->period->id,
        'basic_salary' => 18518519,
        'total_salary' => 16567593,
        'status' => 'calculated',
    ]);

    $result = app(PayrollService::class)->recalculatePayrollForPeriod($this->period);

    expect($result)->toBe('incomplete_attendance');
    expect(Payroll::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
    expect((float) Payroll::query()->value('basic_salary'))->toBe(18518519.0);
});

test('calculate and close actions show the missing-day warning and do not proceed', function () {
    $role = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $admin = User::factory()->create([
        'role_id' => $role->id,
        'status' => 'active',
    ]);

    markAttendance($this->employee, $this->shift, '2026-10-30', 'present');

    $this->actingAs($admin)
        ->from('/admin/payroll-periods/'.$this->period->id)
        ->post(route('admin.payroll-periods.calculate', $this->period))
        ->assertRedirect()
        ->assertSessionHas('error', fn (string $error) => str_contains($error, 'còn 1 ngày chưa có dữ liệu chấm công')
            && str_contains($error, '31/10/2026')
            && str_contains($error, 'trước khi tính lương'));

    expect(Payroll::query()->count())->toBe(0);

    Payroll::create([
        'employee_id' => $this->employee->id,
        'payroll_period_id' => $this->period->id,
        'basic_salary' => 10000000,
        'total_salary' => 10000000,
        'status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->from('/admin/payroll-periods/'.$this->period->id)
        ->post(route('admin.payroll-periods.close', $this->period))
        ->assertRedirect()
        ->assertSessionHas('error', fn (string $error) => str_contains($error, 'còn 1 ngày chưa có dữ liệu chấm công')
            && str_contains($error, 'trước khi đóng kỳ lương'));

    expect(Payroll::query()->value('status'))->toBe('paid');
});
