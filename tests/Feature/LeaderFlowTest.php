<?php

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\TeamMembershipRequest;
use App\Models\User;

beforeEach(function () {
    $this->leaderRole = Role::create(['name' => Role::LEADER, 'description' => 'Trưởng nhóm']);
    $this->employeeRole = Role::create(['name' => Role::EMPLOYEE, 'description' => 'Nhân viên']);
    $this->managerRole = Role::create(['name' => Role::MANAGER, 'description' => 'Quản lý']);

    $this->department = Department::create([
        'department_code' => 'IT-TEST',
        'department_name' => 'Phòng IT Test',
        'status' => 'active',
    ]);

    $this->staffPosition = Position::create([
        'position_name' => 'Nhân viên',
        'base_salary' => 10000000,
        'status' => 'active',
    ]);

    $this->headPosition = Position::create([
        'position_name' => 'Trưởng phòng',
        'base_salary' => 20000000,
        'status' => 'active',
    ]);

    $this->leaderUser = User::factory()->create([
        'role_id' => $this->leaderRole->id,
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $this->leader = Employee::create([
        'user_id' => $this->leaderUser->id,
        'department_id' => $this->department->id,
        'position_id' => $this->staffPosition->id,
        'employee_code' => 'LEAD-001',
        'full_name' => 'Leader Test',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'phone' => '0900000100',
        'email' => 'leader-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->member = Employee::create([
        'department_id' => $this->department->id,
        'position_id' => $this->staffPosition->id,
        'manager_id' => $this->leader->id,
        'employee_code' => 'MEM-001',
        'full_name' => 'Thành viên nhóm',
        'gender' => 'female',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000101',
        'email' => 'member-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->outsider = Employee::create([
        'department_id' => $this->department->id,
        'position_id' => $this->staffPosition->id,
        'employee_code' => 'OUT-001',
        'full_name' => 'Nhân viên ngoài nhóm',
        'gender' => 'male',
        'date_of_birth' => '1994-01-01',
        'phone' => '0900000102',
        'email' => 'outsider-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->departmentHead = Employee::create([
        'department_id' => $this->department->id,
        'position_id' => $this->headPosition->id,
        'employee_code' => 'HEAD-001',
        'full_name' => 'Trưởng phòng IT',
        'gender' => 'male',
        'date_of_birth' => '1988-01-01',
        'phone' => '0900000103',
        'email' => 'head-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->department->update(['manager_id' => $this->departmentHead->id]);

    $this->contractType = ContractType::create([
        'contract_name' => 'Hợp đồng lao động',
        'duration_month' => 12,
    ]);
});

test('leader can access core pages', function () {
    $this->actingAs($this->leaderUser)
        ->get(route('leader.dashboard'))
        ->assertOk();

    $this->actingAs($this->leaderUser)
        ->get(route('leader.employees.index'))
        ->assertOk()
        ->assertSee('Thành viên nhóm')
        ->assertDontSee('Nhân viên ngoài nhóm');

    $this->actingAs($this->leaderUser)
        ->get(route('leader.team-requests.index'))
        ->assertOk();

    $this->actingAs($this->leaderUser)
        ->get(route('leader.team-schedule.index'))
        ->assertOk();

    $this->actingAs($this->leaderUser)
        ->get(route('leader.kpis.index'))
        ->assertOk();

    $this->actingAs($this->leaderUser)
        ->get(route('leader.tasks.index'))
        ->assertOk();

    $this->actingAs($this->leaderUser)
        ->get(route('leader.reports.index'))
        ->assertOk();
});

test('leader can view team member detail but not outsider', function () {
    $this->actingAs($this->leaderUser)
        ->get(route('leader.employees.show', $this->member))
        ->assertOk()
        ->assertSee('Thành viên nhóm');

    $this->actingAs($this->leaderUser)
        ->get(route('leader.employees.show', $this->outsider))
        ->assertForbidden();
});

test('leader cannot propose adding department head to team', function () {
    $this->actingAs($this->leaderUser)
        ->post(route('leader.team-requests.store'), [
            'action' => 'add',
            'employee_id' => $this->departmentHead->id,
            'reason' => 'Test thêm trưởng phòng',
        ])
        ->assertSessionHasErrors('employee_id');

    expect(TeamMembershipRequest::query()->count())->toBe(0);
});

test('leader can propose adding eligible employee and manager can approve', function () {
    $managerUser = User::factory()->create([
        'role_id' => $this->managerRole->id,
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $this->departmentHead->update(['user_id' => $managerUser->id]);

    $this->actingAs($this->leaderUser)
        ->post(route('leader.team-requests.store'), [
            'action' => 'add',
            'employee_id' => $this->outsider->id,
            'reason' => 'Cần thêm vào nhóm',
        ])
        ->assertRedirect(route('leader.team-requests.index'));

    $request = TeamMembershipRequest::query()->first();
    expect($request)->not->toBeNull()
        ->and($request->status)->toBe(TeamMembershipRequest::STATUS_PENDING);

    $this->actingAs($managerUser)
        ->patch(route('manager.team-requests.approve', $request))
        ->assertRedirect(route('manager.team-requests.index'));

    expect($this->outsider->fresh()->manager_id)->toBe($this->leader->id);
});

test('leader can view team contracts only', function () {
    $teamContract = Contract::create([
        'employee_id' => $this->member->id,
        'contract_type_id' => $this->contractType->id,
        'contract_code' => 'HD-TEAM-001',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
    ]);

    $outsideContract = Contract::create([
        'employee_id' => $this->outsider->id,
        'contract_type_id' => $this->contractType->id,
        'contract_code' => 'HD-OUT-001',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'salary' => 12000000,
        'status' => Contract::STATUS_ACTIVE,
    ]);

    $this->actingAs($this->leaderUser)
        ->get(route('leader.contracts.index'))
        ->assertOk()
        ->assertSee('HD-TEAM-001')
        ->assertDontSee('HD-OUT-001');

    $this->actingAs($this->leaderUser)
        ->get(route('leader.contracts.show', $teamContract))
        ->assertOk()
        ->assertSee('HD-TEAM-001');

    $this->actingAs($this->leaderUser)
        ->get(route('leader.contracts.show', $outsideContract))
        ->assertForbidden();
});

test('employee cannot access leader area', function () {
    $employeeUser = User::factory()->create([
        'role_id' => $this->employeeRole->id,
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($employeeUser)
        ->get(route('leader.dashboard'))
        ->assertRedirect(route('employee.dashboard'));
});
