<?php

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\Role;
use App\Models\User;

it('shows only the authenticated employee payrolls', function () {
    $role = Role::create([
        'name' => 'employee',
        'description' => 'Nhân viên',
    ]);

    $user = User::create([
        'role_id' => $role->id,
        'username' => 'employee1',
        'name' => 'Nhân viên 1',
        'email' => 'employee1@example.com',
        'password' => 'password123',
        'status' => 'active',
    ]);

    $employee = Employee::create([
        'user_id' => $user->id,
        'employee_code' => 'NV001',
        'full_name' => 'Nhân viên 1',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '0123456789',
        'email' => 'employee1@example.com',
        'hire_date' => '2023-01-01',
        'status' => 'active',
    ]);

    $otherUser = User::create([
        'role_id' => $role->id,
        'username' => 'employee2',
        'name' => 'Nhân viên 2',
        'email' => 'employee2@example.com',
        'password' => 'password123',
        'status' => 'active',
    ]);

    $otherEmployee = Employee::create([
        'user_id' => $otherUser->id,
        'employee_code' => 'NV002',
        'full_name' => 'Nhân viên 2',
        'gender' => 'female',
        'date_of_birth' => '1991-02-02',
        'phone' => '0987654321',
        'email' => 'employee2@example.com',
        'hire_date' => '2023-02-01',
        'status' => 'active',
    ]);

    $period = PayrollPeriod::create([
        'name' => 'Kỳ lương 06/2026',
        'month' => 6,
        'year' => 2026,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'status' => 'approved',
    ]);

    Payroll::create([
        'employee_id' => $employee->id,
        'payroll_period_id' => $period->id,
        'basic_salary' => 12000000,
        'allowance' => 1000000,
        'bonus' => 500000,
        'deduction' => 200000,
        'total_salary' => 12800000,
    ]);

    Payroll::create([
        'employee_id' => $otherEmployee->id,
        'payroll_period_id' => $period->id,
        'basic_salary' => 14000000,
        'allowance' => 1500000,
        'bonus' => 700000,
        'deduction' => 300000,
        'total_salary' => 15000000,
    ]);

    $response = $this->actingAs($user)->get(route('employee.payrolls.index'));

    $response->assertOk();
    $response->assertSee('Phiếu lương của tôi');
    $response->assertSee('12.800.000đ');
    $response->assertDontSee('15.000.000đ');
});
