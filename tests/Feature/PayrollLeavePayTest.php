<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Services\PayrollService;

beforeEach(function () {
    $this->actingAs(User::factory()->create());

    $this->shift = Shift::create([
        'shift_name' => 'Ca hành chính test',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $position = Position::create([
        'position_name' => 'Nhân viên test',
        'base_salary' => 2000000,
        'status' => 'active',
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
        'position_id' => $position->id,
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

function coverBothDays(Employee $employee, Shift $shift, string $status = 'absent'): void
{
    foreach (['2026-10-30', '2026-10-31'] as $date) {
        Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'attendance_date' => $date,
            'status' => $status,
            'work_ratio' => $status === 'present' ? 1 : 0,
        ]);
    }
}

function approvedLeave(Employee $employee, string $type): void
{
    LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => $type,
        'start_date' => '2026-10-30',
        'end_date' => '2026-10-31',
        'total_days' => 2,
        'reason' => 'Nghi theo don da duyet',
        'status' => LeaveRequest::STATUS_APPROVED,
    ]);
}

test('approved company paid leave keeps full salary even across several days in one month', function (string $type) {
    coverBothDays($this->employee, $this->shift);
    approvedLeave($this->employee, $type);

    expect(app(PayrollService::class)->calculatePayrollForPeriod($this->period))->toBe('success');

    $payroll = Payroll::query()->first();

    expect((float) $payroll->basic_salary)->toBe(2000000.0)
        ->and((float) $payroll->deduction)->toBe(0.0)
        ->and((int) $payroll->paid_leave_days)->toBe(2)
        ->and((int) $payroll->unpaid_leave_days)->toBe(0)
        ->and((float) $payroll->total_salary)->toBe(2000000.0);
})->with(['annual', 'wedding', 'bereavement', 'compensatory', 'business_trip']);

test('approved insurance leave is not paid by the company and is not fined', function () {
    coverBothDays($this->employee, $this->shift);
    approvedLeave($this->employee, 'sick');

    expect(app(PayrollService::class)->calculatePayrollForPeriod($this->period))->toBe('success');

    $payroll = Payroll::query()->first();

    expect((float) $payroll->basic_salary)->toBe(0.0)
        ->and((float) $payroll->deduction)->toBe(0.0)
        ->and((int) $payroll->paid_leave_days)->toBe(0)
        ->and((int) $payroll->unpaid_leave_days)->toBe(0);
});

test('approved unpaid leave drops the day wage without the 300k fine', function () {
    coverBothDays($this->employee, $this->shift);
    approvedLeave($this->employee, 'unpaid');

    expect(app(PayrollService::class)->calculatePayrollForPeriod($this->period))->toBe('success');

    $payroll = Payroll::query()->first();

    expect((float) $payroll->basic_salary)->toBe(0.0)
        ->and((float) $payroll->deduction)->toBe(0.0)
        ->and((int) $payroll->unpaid_leave_days)->toBe(0);
});

test('absence without an approved request loses the day wage and is fined 300k per day', function () {
    coverBothDays($this->employee, $this->shift);

    expect(app(PayrollService::class)->calculatePayrollForPeriod($this->period))->toBe('success');

    $payroll = Payroll::query()->first();

    expect((float) $payroll->basic_salary)->toBe(0.0)
        ->and((float) $payroll->deduction)->toBe(600000.0)
        ->and((int) $payroll->unpaid_leave_days)->toBe(2);
});
