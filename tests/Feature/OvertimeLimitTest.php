<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Position;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use App\Services\OvertimeLimitService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');

    Config::set('overtime.extended_annual_limit', false);

    $this->limitService = app(OvertimeLimitService::class);

    $this->department = Department::create([
        'department_code' => 'PB-OT-'.random_int(100, 999),
        'department_name' => 'Phòng OT test',
        'max_employees' => 20,
        'status' => 'active',
    ]);

    $this->position = Position::create([
        'position_name' => 'Nhân viên',
        'description' => null,
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'NV-OT-'.random_int(1000, 9999),
        'full_name' => 'Nhân viên OT test',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '09'.random_int(10000000, 99999999),
        'email' => 'ot-'.random_int(1000, 9999).'@example.com',
        'department_id' => $this->department->id,
        'position_id' => $this->position->id,
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function createShift(string $name, string $start, string $end): Shift
{
    return Shift::create([
        'shift_name' => $name,
        'start_time' => $start,
        'end_time' => $end,
    ]);
}

function assignShift(Employee $employee, Shift $shift, string $workDate): void
{
    EmployeeShift::create([
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'work_date' => $workDate,
    ]);
}

function createBlockingLeave(
    Employee $employee,
    string $leaveType,
    string $date,
    string $status = LeaveRequest::STATUS_APPROVED,
    ?string $halfDayPeriod = null,
): LeaveRequest {
    return LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => $leaveType,
        'start_date' => $date,
        'end_date' => $date,
        'half_day_period' => $halfDayPeriod,
        'total_days' => $leaveType === 'half_day' ? 0.5 : 1,
        'reason' => 'Test leave conflict',
        'status' => $status,
    ]);
}

function createOvertimeRequest(Employee $employee, string $workDate, float $hours, string $status = OvertimeRequest::STATUS_PENDING): OvertimeRequest
{
    return OvertimeRequest::create([
        'employee_id' => $employee->id,
        'work_date' => $workDate,
        'start_time' => '18:00',
        'end_time' => sprintf('%02d:00', 18 + (int) $hours),
        'total_hours' => $hours,
        'rate_multiplier' => 1.5,
        'reason' => 'Test OT limit',
        'status' => $status,
    ]);
}

function employeeOvertimeFormData(array $overrides = []): array
{
    return array_merge([
        'voluntary_consent' => '1',
    ], $overrides);
}

test('1: OT ngày thường vượt 50% giờ làm bình thường bị chặn', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '22:30',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành báo cáo cuối ngày',
    ]));

    $response->assertSessionHasErrors('start_time');
    $this->assertDatabaseMissing('overtime_requests', [
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
    ]);
});

test('2: tổng giờ làm + OT vượt 12h/ngày bị chặn', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 10 giờ', '08:00:00', '18:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:30',
        'rate_multiplier' => '1.5',
        'reason' => 'Xử lý đơn hàng gấp',
    ]));

    $response->assertSessionHasErrors('start_time');
    expect(session('errors')->get('start_time')[0])->toContain('12h');
    $this->assertDatabaseMissing('overtime_requests', [
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
    ]);
});

test('2b: tổng giờ làm + OT trong giới hạn 12h/ngày được chấp nhận', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 10 giờ', '08:00:00', '18:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Xử lý đơn hàng gấp',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.overtime-requests'));
    $this->assertDatabaseHas('overtime_requests', [
        'employee_id' => $this->employee->id,
        'total_hours' => 2,
    ]);
});

test('1b: OT ngày thường trong giới hạn 50% được chấp nhận', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '22:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành báo cáo cuối ngày',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.overtime-requests'));
    $this->assertDatabaseHas('overtime_requests', [
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21 00:00:00',
        'rate_multiplier' => 1.5,
        'status' => OvertimeRequest::STATUS_PENDING,
    ]);
});

test('daily overtime limit is 50 percent of assigned shift hours', function () {
    $shift = createShift('Ca 6 giờ', '08:00:00', '14:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    expect($this->limitService->normalWorkHoursForDay($this->employee->id, Carbon::parse('2026-09-21')))->toBe(6.0)
        ->and($this->limitService->maxOvertimeHoursForDay($this->employee->id, '2026-09-21'))->toBe(3.0);

    $violations = $this->limitService->violations($this->employee->id, '2026-09-21', 3.5);

    expect($violations)->toHaveKey('start_time')
        ->and($violations['start_time'])->toContain('50%');
});

test('daily overtime limit defaults to eight hours when no shift assigned', function () {
    expect($this->limitService->normalWorkHoursForDay($this->employee->id, Carbon::parse('2026-09-21')))->toBe(8.0)
        ->and($this->limitService->maxOvertimeHoursForDay($this->employee->id, '2026-09-21'))->toBe(4.0);
});

test('twelve hour daily cap limits overtime when normal hours are high', function () {
    $shift = createShift('Ca 10 giờ', '08:00:00', '18:00:00');
    assignShift($this->employee, $shift, '2026-09-21');

    expect($this->limitService->maxOvertimeHoursForDay($this->employee->id, '2026-09-21'))->toBe(2.0);

    $violations = $this->limitService->violations($this->employee->id, '2026-09-21', 2.5);

    expect($violations)->toHaveKey('start_time')
        ->and($violations['start_time'])->toContain('12h');
});

test('3: cộng dồn vượt 40h/tháng bị chặn', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-09-01', 38, OvertimeRequest::STATUS_APPROVED);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '21:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn tất công việc cuối tháng',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('Còn 2 giờ tháng này');
    $this->assertDatabaseMissing('overtime_requests', [
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
    ]);
});

test('3b: cộng dồn trong giới hạn 40h/tháng được chấp nhận', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-09-01', 38, OvertimeRequest::STATUS_APPROVED);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn tất công việc cuối tháng',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.overtime-requests'));
    expect($this->limitService->remainingMonthlyHours($this->employee->id, '2026-09-21'))->toBe(0.0);
});

test('monthly overtime limit is forty hours', function () {
    createOvertimeRequest($this->employee, '2026-09-01', 38, OvertimeRequest::STATUS_APPROVED);

    $violations = $this->limitService->violations($this->employee->id, '2026-09-21', 3);

    expect($violations)->toHaveKey('work_date')
        ->and($violations['work_date'])->toContain('40')
        ->and($violations['work_date'])->toContain('Còn 2 giờ tháng này');
});

function seedYearlyOvertimeHours(Employee $employee, float $totalHours, int $year = 2026): void
{
    $remaining = $totalHours;
    $month = 1;

    while ($remaining > 0 && $month <= 12) {
        $chunk = min(40, $remaining);
        createOvertimeRequest(
            $employee,
            sprintf('%d-%02d-15', $year, $month),
            $chunk,
            OvertimeRequest::STATUS_APPROVED,
        );
        $remaining = round($remaining - $chunk, 2);
        $month++;
    }
}

test('4: cộng dồn vượt 200h/năm bị chặn', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-01-15', 198, OvertimeRequest::STATUS_COMPLETED);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '21:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn tất dự án cuối năm',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('200')
        ->and(session('errors')->get('work_date')[0])->toContain('Còn 2 giờ năm nay');
    $this->assertDatabaseMissing('overtime_requests', [
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
    ]);
});

test('4b: cộng dồn trong giới hạn 200h/năm được chấp nhận', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-01-15', 198, OvertimeRequest::STATUS_COMPLETED);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn tất dự án cuối năm',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.overtime-requests'));
    expect($this->limitService->remainingYearlyHours($this->employee->id, '2026-09-21'))->toBe(0.0);
});

test('4c: cộng dồn vượt 300h/năm ngành đặc thù bị chặn', function () {
    Config::set('overtime.extended_annual_limit', true);

    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    seedYearlyOvertimeHours($this->employee, 298);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '21:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Tăng ca sản xuất xuất khẩu',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('300')
        ->and(session('errors')->get('work_date')[0])->toContain('ngành đặc thù')
        ->and(session('errors')->get('work_date')[0])->toContain('Còn 2 giờ năm nay');
});

test('annual overtime limit is two hundred hours by default', function () {
    createOvertimeRequest($this->employee, '2026-01-15', 198, OvertimeRequest::STATUS_COMPLETED);

    $violations = $this->limitService->violations($this->employee->id, '2026-09-21', 3);

    expect($violations)->toHaveKey('work_date')
        ->and($violations['work_date'])->toContain('200')
        ->and($violations['work_date'])->toContain('Còn 2 giờ năm nay');
});

test('extended annual overtime limit allows three hundred hours', function () {
    Config::set('overtime.extended_annual_limit', true);

    seedYearlyOvertimeHours($this->employee, 298);

    expect($this->limitService->violations($this->employee->id, '2026-09-21', 2))->toBeEmpty()
        ->and($this->limitService->violations($this->employee->id, '2026-09-21', 3))->not->toBeEmpty();
});

test('rejected overtime requests do not count toward limits', function () {
    createOvertimeRequest($this->employee, '2026-09-21', 4, OvertimeRequest::STATUS_REJECTED);

    expect($this->limitService->violations($this->employee->id, '2026-09-21', 4))->toBeEmpty();
});

test('multiple shifts on same day sum normal hours', function () {
    $morning = createShift('Ca sáng', '08:00:00', '12:00:00');
    $afternoon = createShift('Ca chiều', '13:00:00', '17:00:00');
    assignShift($this->employee, $morning, '2026-09-21');
    assignShift($this->employee, $afternoon, '2026-09-21');

    expect($this->limitService->normalWorkHoursForDay($this->employee->id, Carbon::parse('2026-09-21')))->toBe(8.0)
        ->and($this->limitService->maxOvertimeHoursForDay($this->employee->id, '2026-09-21'))->toBe(4.0);
});

test('5: warns when monthly overtime reaches eighty percent threshold', function () {
    createOvertimeRequest($this->employee, '2026-09-10', 32, OvertimeRequest::STATUS_APPROVED);

    $warnings = $this->limitService->warnings($this->employee->id, '2026-09-21');

    expect($warnings)->not->toBeEmpty()
        ->and($warnings[0])->toContain('32h')
        ->and($warnings[0])->toContain('80%');
});

test('5: warns when yearly overtime reaches eighty percent threshold', function () {
    createOvertimeRequest($this->employee, '2026-01-15', 160, OvertimeRequest::STATUS_APPROVED);

    $warnings = $this->limitService->warnings($this->employee->id, '2026-09-21');

    expect($warnings)->not->toBeEmpty()
        ->and(collect($warnings)->join(' '))->toContain('160h')
        ->and(collect($warnings)->join(' '))->toContain('80%');
});

test('5: reminds labor department notification from two hundred hours per year', function () {
    seedYearlyOvertimeHours($this->employee, 200);

    $warnings = $this->limitService->warnings($this->employee->id, '2026-09-21');

    expect(collect($warnings)->join(' '))->toContain('Sở LĐTBXH');
});

test('5: overtime create page shows threshold warnings', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    createOvertimeRequest($this->employee, '2026-09-10', 32, OvertimeRequest::STATUS_APPROVED);
    seedYearlyOvertimeHours($this->employee, 200);

    $response = $this->actingAs($user)->get(route('employee.overtime-requests.create'));

    $response->assertOk()
        ->assertSee('Cảnh báo giới hạn tăng ca')
        ->assertSee('32h')
        ->assertSee('Sở LĐTBXH');
});

test('5b: no warnings below eighty percent thresholds', function () {
    createOvertimeRequest($this->employee, '2026-09-10', 20, OvertimeRequest::STATUS_APPROVED);
    createOvertimeRequest($this->employee, '2026-01-15', 100, OvertimeRequest::STATUS_APPROVED);

    expect($this->limitService->warnings($this->employee->id, '2026-09-21'))->toBeEmpty();
});

test('6: pending overtime requests count toward daily cap', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-09-21', 3, OvertimeRequest::STATUS_PENDING);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '19:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hỗ trợ thêm ca tối',
    ]));

    $response->assertSessionHasErrors('start_time');
    $this->assertDatabaseCount('overtime_requests', 1);
});

test('6b: pending overtime requests count toward monthly cap', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    $shift = createShift('Ca 8 giờ', '08:00:00', '16:00:00');
    assignShift($this->employee, $shift, '2026-09-21');
    createOvertimeRequest($this->employee, '2026-09-01', 38, OvertimeRequest::STATUS_APPROVED);
    createOvertimeRequest($this->employee, '2026-09-10', 2, OvertimeRequest::STATUS_PENDING);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Tăng ca cuối tháng',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('Còn 0 giờ tháng này');
    $this->assertDatabaseCount('overtime_requests', 2);
});

test('6c: pending overtime requests count toward yearly cap and warnings', function () {
    createOvertimeRequest($this->employee, '2026-01-15', 198, OvertimeRequest::STATUS_PENDING);

    expect($this->limitService->violations($this->employee->id, '2026-09-21', 3))->toHaveKey('work_date');

    createOvertimeRequest($this->employee, '2026-02-15', 2, OvertimeRequest::STATUS_PENDING);

    expect(collect($this->limitService->warnings($this->employee->id, '2026-09-21'))->join(' '))->toContain('Sở LĐTBXH');
});

test('7: overlapping overtime time range is blocked', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    OvertimeRequest::create([
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'total_hours' => 2,
        'rate_multiplier' => 1.5,
        'reason' => 'Hoàn thành công việc buổi tối',
        'status' => OvertimeRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '19:00',
        'end_time' => '21:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT buổi tối bổ sung',
    ]));

    $response->assertSessionHasErrors('start_time');
    expect(session('errors')->get('start_time')[0])->toContain('trùng');
    $this->assertDatabaseCount('overtime_requests', 1);
});

test('7b: non overlapping overtime on same day is allowed', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    OvertimeRequest::create([
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'total_hours' => 2,
        'rate_multiplier' => 1.5,
        'reason' => 'Hoàn thành ca OT thứ nhất',
        'status' => OvertimeRequest::STATUS_APPROVED,
    ]);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '20:00',
        'end_time' => '22:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành ca OT thứ hai',
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employee.overtime-requests'));
    $this->assertDatabaseCount('overtime_requests', 2);
});

test('7c: rejected overtime does not block overlapping submission', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    OvertimeRequest::create([
        'employee_id' => $this->employee->id,
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'total_hours' => 2,
        'rate_multiplier' => 1.5,
        'reason' => 'OT bị từ chối',
        'status' => OvertimeRequest::STATUS_REJECTED,
    ]);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '19:00',
        'end_time' => '21:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT thay thế',
    ]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('overtime_requests', 2);
});

test('8: overtime is blocked on approved annual leave day', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'annual', '2026-09-21');

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT khi nghỉ phép',
    ]));

    $response->assertSessionHasErrors('work_date');
    $this->assertDatabaseMissing('overtime_requests', ['employee_id' => $this->employee->id]);
});

test('8b: overtime is blocked on pending sick leave day', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'sick', '2026-09-21', LeaveRequest::STATUS_PENDING);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT khi nghỉ ốm',
    ]));

    $response->assertSessionHasErrors('work_date');
});

test('8c: overtime is blocked for maternity and unpaid leave on same day', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);

    foreach (['maternity', 'unpaid'] as $leaveType) {
        LeaveRequest::query()->where('employee_id', $this->employee->id)->delete();

        createBlockingLeave($this->employee, $leaveType, '2026-09-21');

        $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
            'work_date' => '2026-09-21',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'rate_multiplier' => '1.5',
            'reason' => 'OT test '.$leaveType,
        ]));

        $response->assertSessionHasErrors('work_date');
    }
});

test('8d: half day morning leave blocks overlapping overtime only', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'half_day', '2026-09-21', LeaveRequest::STATUS_APPROVED, LeaveRequest::HALF_DAY_MORNING);

    $blocked = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '09:00',
        'end_time' => '11:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành công việc buổi sáng',
    ]));
    $blocked->assertSessionHasErrors('work_date');

    $allowedAfternoon = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '14:00',
        'end_time' => '16:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành công việc buổi chiều',
    ]));
    $allowedAfternoon->assertSessionHasNoErrors();

    $allowedEvening = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành công việc buổi tối',
    ]));
    $allowedEvening->assertSessionHasNoErrors();
    $this->assertDatabaseCount('overtime_requests', 2);
});

test('8d afternoon: half day leave blocks overlapping overtime only outside leave window', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'half_day', '2026-09-21', LeaveRequest::STATUS_APPROVED, LeaveRequest::HALF_DAY_AFTERNOON);

    $blocked = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '14:00',
        'end_time' => '16:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành công việc buổi chiều',
    ]));
    $blocked->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('Chỉ được OT ngoài khoảng giờ đã nghỉ');

    $allowed = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'Hoàn thành công việc buổi tối',
    ]));
    $allowed->assertSessionHasNoErrors();
});

test('8f: overtime create page shows half day leave allowed window notice', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'half_day', '2026-09-21', LeaveRequest::STATUS_PENDING, LeaveRequest::HALF_DAY_MORNING);

    $response = $this->actingAs($user)->get(route('employee.overtime-requests.create', [
        'work_date' => '2026-09-21',
    ]));

    $response->assertOk()
        ->assertSee('Nghỉ nửa ngày')
        ->assertSee('08:00')
        ->assertSee('12:00')
        ->assertSee('ngoài');
});

test('8e: rejected leave does not block overtime', function () {
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $this->employee->update(['user_id' => $user->id]);
    createBlockingLeave($this->employee, 'unpaid', '2026-09-21', LeaveRequest::STATUS_REJECTED);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT sau khi đơn nghỉ bị từ chối',
    ]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('overtime_requests', 1);
});

function linkEmployeeToUser(Employee $employee): User
{
    $employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Employee']);
    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ]);
    $employee->update(['user_id' => $user->id]);

    return $user;
}

test('11a: pregnant from month 7 is prohibited from overtime', function () {
    $this->employee->update([
        'gender' => 'female',
        'overtime_ban_status' => Employee::OT_BAN_PREGNANCY_7M,
    ]);
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT khi mang thai tháng 7+',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('Điều 137');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('11b: nursing mother under 12 months is prohibited from overtime', function () {
    $this->employee->update([
        'gender' => 'female',
        'overtime_ban_status' => Employee::OT_BAN_NURSING_UNDER_12M,
    ]);
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT khi nuôi con dưới 12 tháng',
    ]));

    $response->assertSessionHasErrors('work_date');
    expect(session('errors')->get('work_date')[0])->toContain('nuôi con dưới 12 tháng');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('11c: employee without overtime ban can submit overtime', function () {
    $this->employee->update([
        'gender' => 'female',
        'overtime_ban_status' => null,
    ]);
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT bình thường',
    ]));

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('overtime_requests', 1);
});

test('11d: admin cannot set overtime ban on male employee', function () {
    $adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)->put(route('admin.employees.update', $this->employee), [
        'employee_code' => $this->employee->employee_code,
        'full_name' => $this->employee->full_name,
        'gender' => 'male',
        'date_of_birth' => $this->employee->date_of_birth->format('Y-m-d'),
        'phone' => $this->employee->phone,
        'email' => $this->employee->email,
        'department_id' => $this->employee->department_id,
        'position_id' => $this->employee->position_id,
        'hire_date' => $this->employee->hire_date->format('Y-m-d'),
        'status' => 'active',
        'overtime_ban_status' => Employee::OT_BAN_PREGNANCY_7M,
    ]);

    $response->assertSessionHasErrors('overtime_ban_status');
    expect($this->employee->fresh()->overtime_ban_status)->toBeNull();
});

test('11e: overtime create page shows article 137 prohibition notice', function () {
    $this->employee->update([
        'gender' => 'female',
        'overtime_ban_status' => Employee::OT_BAN_PREGNANCY_7M,
    ]);
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->get(route('employee.overtime-requests.create'));

    $response->assertOk()
        ->assertSee('Cấm tăng ca theo Điều 137 BLLĐ')
        ->assertSee('Mang thai từ tháng thứ 7');
});

test('13a: overtime submission requires voluntary consent checkbox', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), [
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT không có đồng thuận',
    ]);

    $response->assertSessionHasErrors('voluntary_consent');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('13b: voluntary consent is stored on overtime request', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT có đồng thuận tự nguyện',
    ]));

    $response->assertSessionHasNoErrors();
    $request = OvertimeRequest::query()->where('employee_id', $this->employee->id)->first();
    expect($request)->not->toBeNull();
    expect($request->voluntary_consent_at)->not->toBeNull();
});

test('13c: overtime create page shows voluntary consent checkbox', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->get(route('employee.overtime-requests.create'));

    $response->assertOk()
        ->assertSee('đồng ý làm thêm giờ tự nguyện', false);
});

test('14a: overtime submission requires reason and work description', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => '',
    ]));

    $response->assertSessionHasErrors('reason');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('14b: whitespace-only reason is rejected', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => '     ',
    ]));

    $response->assertSessionHasErrors('reason');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('14c: reason shorter than minimum length is rejected', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->post(route('employee.overtime-requests.store'), employeeOvertimeFormData([
        'work_date' => '2026-09-21',
        'start_time' => '18:00',
        'end_time' => '20:00',
        'rate_multiplier' => '1.5',
        'reason' => 'OT gấp',
    ]));

    $response->assertSessionHasErrors('reason');
    expect(session('errors')->get('reason')[0])->toContain('Sở LĐTBXH');
    $this->assertDatabaseCount('overtime_requests', 0);
});

test('14d: overtime create page shows reason and work description requirement', function () {
    $user = linkEmployeeToUser($this->employee);

    $response = $this->actingAs($user)->get(route('employee.overtime-requests.create'));

    $response->assertOk()
        ->assertSee('Lý do / công việc cần làm')
        ->assertSee('Sở LĐTBXH');
});
