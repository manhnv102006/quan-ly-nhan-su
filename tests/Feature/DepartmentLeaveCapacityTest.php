<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestHistory;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\DepartmentLeaveCapacityService;
use App\Support\LeaveCapacityRules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Carbon::setTestNow('2026-09-21');

    $this->capacityService = app(DepartmentLeaveCapacityService::class);

    $this->department = Department::create([
        'department_code' => 'PB-CAP-'.random_int(100, 999),
        'department_name' => 'Phòng capacity test',
        'max_employees' => 20,
        'status' => 'active',
    ]);

    $this->position = Position::create([
        'position_name' => 'Nhân viên',
        'description' => null,
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $this->probationType = ContractType::create([
        'code' => 'PROBATION-CAP',
        'contract_name' => 'Thử việc',
        'category' => ContractType::CATEGORY_PROBATION,
        'duration_month' => 2,
    ]);

    $this->fixedType = ContractType::create([
        'code' => 'FIXED-CAP',
        'contract_name' => 'Chính thức',
        'category' => ContractType::CATEGORY_FIXED,
        'duration_month' => 12,
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

function createCapacityEmployee(Department $department, Position $position, int $index, ?ContractType $contractType = null): Employee
{
    $employee = Employee::create([
        'employee_code' => 'NV-CAP-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT).'-'.random_int(100, 999),
        'full_name' => "Nhân viên capacity {$index}",
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '09'.random_int(10000000, 99999999),
        'email' => 'cap-'.$index.'-'.random_int(1000, 9999).'@example.com',
        'department_id' => $department->id,
        'position_id' => $position->id,
        'hire_date' => '2025-01-01',
        'status' => 'active',
    ]);

    if ($contractType) {
        Contract::create([
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'contract_type_id' => $contractType->id,
            'contract_code' => 'HD-CAP-'.$employee->id,
            'start_date' => '2025-01-01',
            'end_date' => '2027-12-31',
            'salary' => 10000000,
            'status' => Contract::STATUS_ACTIVE,
        ]);
    }

    return $employee;
}

function createApprovedLeave(Employee $employee, string $start, string $end, float $days, string $type = 'annual'): LeaveRequest
{
    return LeaveRequest::create([
        'employee_id' => $employee->id,
        'leave_type' => $type,
        'start_date' => $start,
        'end_date' => $end,
        'total_days' => $days,
        'reason' => 'Test capacity',
        'status' => LeaveRequest::STATUS_APPROVED,
        'approved_at' => now(),
    ]);
}

test('slotsFor uses floor with minimum one person', function () {
    expect(LeaveCapacityRules::slotsFor(10, LeaveCapacityRules::RATIO_EMPLOYEE))->toBe(3)
        ->and(LeaveCapacityRules::slotsFor(3, LeaveCapacityRules::RATIO_EMPLOYEE))->toBe(1)
        ->and(LeaveCapacityRules::slotsFor(10, LeaveCapacityRules::RATIO_MANAGER_ACCOUNTANT))->toBe(2);
});

test('leave from 12 working days is exempt from department capacity', function () {
    expect(LeaveCapacityRules::countsTowardDepartmentCapacity('annual', 11))->toBeTrue()
        ->and(LeaveCapacityRules::countsTowardDepartmentCapacity('annual', 12))->toBeFalse()
        ->and(LeaveCapacityRules::countsTowardDepartmentCapacity('annual', 15))->toBeFalse();
});

test('statutory leave types are exempt from department capacity', function () {
    foreach (LeaveCapacityRules::STATUTORY_CAPACITY_EXEMPT_LEAVE_TYPES as $type) {
        expect(LeaveCapacityRules::countsTowardDepartmentCapacity($type, 3))->toBeFalse();
    }
});

test('only approved leave counts toward department capacity', function () {
    for ($i = 1; $i <= 10; $i++) {
        createCapacityEmployee($this->department, $this->position, $i, $this->fixedType);
    }

    $employees = Employee::query()->where('department_id', $this->department->id)->orderBy('id')->get();

    createApprovedLeave($employees[0], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[1], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[2], '2026-10-05', '2026-10-05', 1);

    LeaveRequest::create([
        'employee_id' => $employees[3]->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Pending should not count',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $applicant = $employees[4];

    $blocked = $this->capacityService->submitBlockedMessage(
        $applicant,
        '2026-10-05',
        '2026-10-05',
        1,
        'annual',
    );

    expect($blocked)->not->toBeNull();

    LeaveRequest::query()->where('status', LeaveRequest::STATUS_PENDING)->delete();

    expect($this->capacityService->submitBlockedMessage(
        $applicant,
        '2026-10-05',
        '2026-10-05',
        1,
        'annual',
    ))->not->toBeNull();
});

test('working headcount excludes probation and long maternity leave', function () {
    $regular = createCapacityEmployee($this->department, $this->position, 1, $this->fixedType);
    $probation = createCapacityEmployee($this->department, $this->position, 2, $this->probationType);
    $maternity = createCapacityEmployee($this->department, $this->position, 3, $this->fixedType);

    createApprovedLeave($maternity, '2026-10-01', '2026-12-31', 60, 'maternity');

    expect($this->capacityService->workingHeadcount($this->department->id, '2026-10-05'))->toBe(1)
        ->and($regular->countsTowardDepartmentWorkingHeadcount('2026-10-05'))->toBeTrue()
        ->and($probation->countsTowardDepartmentWorkingHeadcount('2026-10-05'))->toBeFalse()
        ->and($maternity->countsTowardDepartmentWorkingHeadcount('2026-10-05'))->toBeFalse();
});

test('statutory leave submission is not blocked but shows manager warning context', function () {
    for ($i = 1; $i <= 10; $i++) {
        createCapacityEmployee($this->department, $this->position, $i, $this->fixedType);
    }

    $employees = Employee::query()->where('department_id', $this->department->id)->orderBy('id')->get();

    createApprovedLeave($employees[0], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[1], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[2], '2026-10-05', '2026-10-05', 1);

    $sickEmployee = $employees[3];

    expect($this->capacityService->submitBlockedMessage(
        $sickEmployee,
        '2026-10-05',
        '2026-10-05',
        1,
        'sick',
    ))->toBeNull();

    $pendingSick = LeaveRequest::create([
        'employee_id' => $sickEmployee->id,
        'leave_type' => 'sick',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Nghỉ ốm',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    $context = $this->capacityService->approvalCapacityContext($pendingSick);

    expect($context)->not->toBeNull()
        ->and($context['statutory_exempt'])->toBeTrue()
        ->and($context['blocked'])->toBeFalse()
        ->and($context['warning_message'])->not->toBeNull();
});

test('manager can approve over capacity with override reason when configured', function () {
    Config::set('leave.department_capacity_enforcement', 'override');

    for ($i = 1; $i <= 10; $i++) {
        createCapacityEmployee($this->department, $this->position, $i, $this->fixedType);
    }

    $employees = Employee::query()->where('department_id', $this->department->id)->orderBy('id')->get();

    createApprovedLeave($employees[0], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[1], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[2], '2026-10-05', '2026-10-05', 1);

    $managerRole = Role::create(['name' => Role::MANAGER, 'description' => 'Manager']);
    $managerUser = User::factory()->create(['role_id' => $managerRole->id, 'status' => 'active']);
    $manager = createCapacityEmployee($this->department, $this->position, 99, $this->fixedType);
    $manager->update(['user_id' => $managerUser->id]);
    $this->department->update(['manager_id' => $manager->id]);

    $pending = LeaveRequest::create([
        'employee_id' => $employees[3]->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Cần nghỉ thêm',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    app(\App\Services\LeaveApprovalService::class)->approve(
        $pending,
        $managerUser->id,
        $manager,
        'Dự án gấp, đã bố trí người thay ca',
    );

    $pending->refresh();

    expect($pending->status)->toBe(LeaveRequest::STATUS_APPROVED);

    $history = LeaveRequestHistory::query()
        ->where('leave_request_id', $pending->id)
        ->where('action', 'approved')
        ->first();

    expect($history)->not->toBeNull()
        ->and($history->note)->toContain('[Duyệt vượt giới hạn phòng ban]');
});

test('capacity block mode rejects approval without override', function () {
    Config::set('leave.department_capacity_enforcement', 'block');

    for ($i = 1; $i <= 10; $i++) {
        createCapacityEmployee($this->department, $this->position, $i, $this->fixedType);
    }

    $employees = Employee::query()->where('department_id', $this->department->id)->orderBy('id')->get();

    createApprovedLeave($employees[0], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[1], '2026-10-05', '2026-10-05', 1);
    createApprovedLeave($employees[2], '2026-10-05', '2026-10-05', 1);

    $managerRole = Role::create(['name' => Role::MANAGER, 'description' => 'Manager']);
    $managerUser = User::factory()->create(['role_id' => $managerRole->id, 'status' => 'active']);
    $manager = createCapacityEmployee($this->department, $this->position, 98, $this->fixedType);
    $manager->update(['user_id' => $managerUser->id]);
    $this->department->update(['manager_id' => $manager->id]);

    $pending = LeaveRequest::create([
        'employee_id' => $employees[3]->id,
        'leave_type' => 'annual',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-05',
        'total_days' => 1,
        'reason' => 'Không duyệt được',
        'status' => LeaveRequest::STATUS_PENDING,
    ]);

    expect(fn () => app(\App\Services\LeaveApprovalService::class)->approve(
        $pending,
        $managerUser->id,
        $manager,
        'Lý do vẫn bị chặn',
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});
