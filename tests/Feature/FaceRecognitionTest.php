<?php

use App\Models\Employee;
use App\Models\EmployeeFaceDescriptor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('services.face.kiosk_token', 'test-kiosk-token-secret');

    $this->adminRole = Role::create(['name' => Role::ADMIN, 'description' => 'Admin']);
    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
        'status' => 'active',
    ]);

    $this->employee = Employee::create([
        'employee_code' => 'NV-FACE-001',
        'full_name' => 'Nguyễn Văn Face',
        'gender' => 'male',
        'date_of_birth' => '1995-01-01',
        'phone' => '0900000099',
        'email' => 'face-test@example.com',
        'hire_date' => now()->toDateString(),
        'status' => 'active',
    ]);
});

test('face api rejects requests without valid kiosk token', function () {
    $response = $this->getJson('/api/face/descriptors');

    $response->assertUnauthorized()
        ->assertJson(['success' => false]);
});

test('face api returns descriptors for kiosk sync', function () {
    $embedding = array_fill(0, 512, 0.01);

    EmployeeFaceDescriptor::create([
        'employee_id' => $this->employee->id,
        'embedding' => $embedding,
        'model_name' => 'buffalo_l',
    ]);

    $response = $this->withHeader('X-Face-Token', 'test-kiosk-token-secret')
        ->getJson('/api/face/descriptors');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.employee_id', $this->employee->id)
        ->assertJsonPath('data.0.employee_code', 'NV-FACE-001');
});

test('face api stores descriptor from enroll tool', function () {
    $embedding = array_fill(0, 512, 0.02);

    $response = $this->withHeader('X-Face-Token', 'test-kiosk-token-secret')
        ->postJson('/api/face/descriptors', [
            'employee_id' => $this->employee->id,
            'embedding' => $embedding,
            'quality' => 5,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true);

    expect(EmployeeFaceDescriptor::query()->where('employee_id', $this->employee->id)->count())->toBe(1);
});

test('admin can view face enrollment management page', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.face-enrollments.index'));

    $response->assertOk()
        ->assertSee('Chấm công bằng khuôn mặt')
        ->assertSee('NV-FACE-001');
});

test('admin can delete employee face descriptors', function () {
    EmployeeFaceDescriptor::create([
        'employee_id' => $this->employee->id,
        'embedding' => array_fill(0, 512, 0.03),
        'model_name' => 'buffalo_l',
    ]);

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.face-enrollments.destroy', $this->employee));

    $response->assertRedirect(route('admin.face-enrollments.index'));
    expect(EmployeeFaceDescriptor::query()->where('employee_id', $this->employee->id)->count())->toBe(0);
});
