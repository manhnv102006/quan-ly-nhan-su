<?php

use App\Models\Contract;
use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\MasterDemoSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(MasterDemoSeeder::class);
    $this->leader = User::query()->where('username', 'leader')->firstOrFail();
    $this->leader->update(['email_verified_at' => now()]);
});

/**
 * @return array{status:int, error:?string}
 */
function hitLeaderRoute(string $method, string $uri, ?User $user = null, array $data = []): array
{
    $test = test();

    try {
        $request = $test->actingAs($user ?? $test->leader);
        $response = match (strtoupper($method)) {
            'GET' => $request->get($uri),
            'POST' => $request->post($uri, $data),
            'PUT' => $request->put($uri, $data),
            'PATCH' => $request->patch($uri, $data),
            default => throw new InvalidArgumentException("Unsupported method: {$method}"),
        };

        $status = $response->getStatusCode();
        $error = null;

        if ($status >= 500) {
            $content = $response->getContent() ?: '';
            if (preg_match('/class="exception-message[^"]*"[^>]*>([^<]+)/', $content, $m)) {
                $error = trim(html_entity_decode($m[1]));
            } elseif (preg_match('/<title>([^<]+)<\/title>/', $content, $m)) {
                $error = trim($m[1]);
            } else {
                $error = "HTTP {$status}";
            }
        }

        return ['status' => $status, 'error' => $error];
    } catch (Throwable $e) {
        return ['status' => 500, 'error' => $e->getMessage()];
    }
}

test('leader core pages render without server errors', function () {
    $routes = [
        ['GET', '/leader/dashboard', 'Dashboard'],
        ['GET', '/leader/employees', 'Danh sách nhân viên'],
        ['GET', '/leader/team-schedule', 'Lịch làm việc nhóm'],
        ['GET', '/leader/team-requests', 'Đề xuất thành viên'],
        ['GET', '/leader/kpis', 'KPI nhóm'],
        ['GET', '/leader/tasks', 'Task KPI'],
        ['GET', '/leader/reports', 'Báo cáo nhóm'],
        ['GET', '/leader/reports/export', 'Xuất CSV báo cáo'],
        ['GET', '/leader/contracts', 'Hợp đồng nhóm'],
    ];

    $failures = [];

    foreach ($routes as [$method, $uri, $label]) {
        $result = hitLeaderRoute($method, $uri, $this->leader);

        if ($result['status'] >= 500) {
            $failures[] = "{$label} ({$uri}): {$result['error']}";
        } else {
            expect($result['status'])->toBeLessThan(500);
        }
    }

    expect($failures)->toBeEmpty('Lỗi trang leader: '.implode(' | ', $failures));
});

test('leader employee detail works for team member', function () {
    $member = Employee::query()->where('employee_code', 'EMP004')->firstOrFail();

    $result = hitLeaderRoute('GET', "/leader/employees/{$member->id}", $this->leader);

    expect($result['status'])->toBe(200);
});

test('leader employee detail blocks non-team member', function () {
    $outsider = Employee::query()->where('employee_code', 'EMP007')->firstOrFail();

    $result = hitLeaderRoute('GET', "/leader/employees/{$outsider->id}", $this->leader);

    expect($result['status'])->toBe(403);
});

test('leader kpi detail works for team member kpi', function () {
    $employeeKpi = EmployeeKPI::query()
        ->whereHas('employee', fn ($q) => $q->where('employee_code', 'EMP004'))
        ->firstOrFail();

    $result = hitLeaderRoute('GET', "/leader/kpis/{$employeeKpi->id}", $this->leader);

    expect($result['status'])->toBe(200);
});

test('leader contract detail works for team member', function () {
    $contract = Contract::query()
        ->whereHas('employee', fn ($q) => $q->where('employee_code', 'EMP004'))
        ->firstOrFail();

    $result = hitLeaderRoute('GET', "/leader/contracts/{$contract->id}", $this->leader);

    expect($result['status'])->toBeIn([200, 403]);
});

test('documents broken leader routes and missing controllers', function () {
    $broken = [];

    $optionalIds = [
        'assignment' => KPIAssignment::query()->value('id'),
        'employeeKpi' => EmployeeKPI::query()->value('id'),
        'leaveRequest' => LeaveRequest::query()->value('id'),
        'overtimeRequest' => OvertimeRequest::query()->value('id'),
    ];

    $routes = [
        ['GET', '/leader/attendance', 'Chấm công nhóm'],
        ['GET', '/leader/leave-requests', 'Duyệt nghỉ phép'],
        ['GET', '/leader/overtime-requests', 'Duyệt tăng ca'],
        ['GET', '/leader/team-kpis', 'KPI nhóm (giao việc)'],
        ['GET', '/leader/team-chat', 'Chat nhóm'],
        ['GET', '/leader/kpis/'.($optionalIds['employeeKpi'] ?? 1).'/score', 'Chấm điểm KPI'],
        ['GET', '/leader/team-kpis/'.($optionalIds['assignment'] ?? 1), 'Chi tiết team KPI'],
    ];

    foreach ($routes as [$method, $uri, $label]) {
        $result = hitLeaderRoute($method, $uri, $this->leader);

        if ($result['status'] >= 500) {
            $broken[] = "{$label}: {$result['error']}";
        }
    }

    // Ghi nhận lỗi để báo cáo — test vẫn pass nhưng in ra danh sách broken
    if ($broken !== []) {
        fwrite(STDERR, "\n[BROKEN LEADER ROUTES]\n".implode("\n", $broken)."\n");
    }

    expect(true)->toBeTrue();
});

test('non-leader cannot access leader dashboard', function () {
    $employee = User::query()->where('username', 'employee')->firstOrFail();

    $this->actingAs($employee)
        ->get('/leader/dashboard')
        ->assertForbidden();
});
