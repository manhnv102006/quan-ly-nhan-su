<?php

use App\Models\Contract;
use App\Models\Employee;
use App\Models\EmployeeKPI;
use App\Models\KPIAssignment;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['app.debug' => false]);

$leader = User::query()->where('username', 'leader')->first();
if (! $leader) {
    fwrite(STDERR, "Không tìm thấy user leader. Chạy: php artisan db:seed --class=MasterDemoSeeder\n");
    exit(1);
}

$optionalIds = [
    'employee' => Employee::query()->where('employee_code', 'EMP004')->value('id'),
    'employeeKpi' => EmployeeKPI::query()->value('id'),
    'assignment' => KPIAssignment::query()->value('id'),
    'contract' => Contract::query()->whereHas('employee', fn ($q) => $q->where('employee_code', 'EMP004'))->value('id'),
    'leaveRequest' => LeaveRequest::query()->value('id'),
    'overtimeRequest' => OvertimeRequest::query()->value('id'),
];

$routes = [
    ['GET', '/leader/dashboard', 'Dashboard'],
    ['GET', '/leader/employees', 'Danh sách nhân viên'],
    ['GET', '/leader/employees/'.($optionalIds['employee'] ?? 1), 'Chi tiết nhân viên nhóm'],
    ['GET', '/leader/team-schedule', 'Lịch làm việc nhóm'],
    ['GET', '/leader/team-requests', 'Đề xuất thành viên'],
    ['GET', '/leader/kpis', 'KPI nhóm'],
    ['GET', '/leader/kpis/'.($optionalIds['employeeKpi'] ?? 1), 'Chi tiết KPI'],
    ['GET', '/leader/kpis/'.($optionalIds['employeeKpi'] ?? 1).'/score', 'Chấm điểm KPI'],
    ['GET', '/leader/tasks', 'Task KPI'],
    ['GET', '/leader/reports', 'Báo cáo nhóm'],
    ['GET', '/leader/reports/export', 'Xuất CSV báo cáo'],
    ['GET', '/leader/contracts', 'Hợp đồng nhóm'],
    ['GET', '/leader/contracts/'.($optionalIds['contract'] ?? 1), 'Chi tiết hợp đồng'],
    ['GET', '/leader/attendance', 'Chấm công nhóm'],
    ['GET', '/leader/leave-requests', 'Duyệt nghỉ phép'],
    ['GET', '/leader/leave-requests/'.($optionalIds['leaveRequest'] ?? 1), 'Chi tiết nghỉ phép'],
    ['GET', '/leader/overtime-requests', 'Duyệt tăng ca'],
    ['GET', '/leader/overtime-requests/'.($optionalIds['overtimeRequest'] ?? 1), 'Chi tiết tăng ca'],
    ['GET', '/leader/team-kpis', 'Team KPI'],
    ['GET', '/leader/team-kpis/'.($optionalIds['assignment'] ?? 1), 'Chi tiết Team KPI'],
    ['GET', '/leader/team-kpis/'.($optionalIds['assignment'] ?? 1).'/allocate', 'Phân bổ Team KPI'],
    ['GET', '/leader/team-chat', 'Chat nhóm'],
    ['GET', '/employee/attendance', 'Chấm công cá nhân (sidebar)'],
    ['GET', '/employee/leave-requests', 'Nghỉ phép cá nhân (sidebar)'],
];

$ok = [];
$warn = [];
$fail = [];

Auth::login($leader);

foreach ($routes as [$method, $uri, $label]) {
    try {
        $request = Request::create($uri, $method);
        $request->setUserResolver(fn () => $leader);

        /** @var Response $response */
        $response = $app->handle($request);
        $status = $response->getStatusCode();

        if ($status >= 500) {
            $body = $response->getContent() ?: '';
            $msg = 'HTTP '.$status;
            if (preg_match('/(?:Exception|Error)[^:]*:\s*([^<\n]+)/', $body, $m)) {
                $msg = trim($m[1]);
            }
            $fail[] = ['label' => $label, 'uri' => $uri, 'status' => $status, 'error' => $msg];
        } elseif ($status === 403) {
            $warn[] = ['label' => $label, 'uri' => $uri, 'status' => $status, 'error' => 'Forbidden'];
        } elseif ($status === 404) {
            $fail[] = ['label' => $label, 'uri' => $uri, 'status' => $status, 'error' => 'Not Found'];
        } else {
            $ok[] = ['label' => $label, 'uri' => $uri, 'status' => $status];
        }
    } catch (Throwable $e) {
        $fail[] = ['label' => $label, 'uri' => $uri, 'status' => 500, 'error' => $e->getMessage()];
    }
}

// Kiểm tra controller tồn tại
$missingControllers = [];
$controllerChecks = [
    'App\\Http\\Controllers\\Leader\\AttendanceController',
    'App\\Http\\Controllers\\Leader\\LeaveApprovalController',
    'App\\Http\\Controllers\\Leader\\OvertimeApprovalController',
    'App\\Http\\Controllers\\Leader\\TeamKpiController',
    'App\\Http\\Controllers\\TeamChatController',
    'App\\Http\\Controllers\\Manager\\TeamController',
    'App\\Http\\Controllers\\Manager\\LeaderTeamReportController',
];

foreach ($controllerChecks as $class) {
    if (! class_exists($class)) {
        $missingControllers[] = $class;
    }
}

// Kiểm tra method thiếu
$missingMethods = [];
if (class_exists(\App\Http\Controllers\Leader\KPIController::class)) {
    foreach (['editScore', 'updateScore'] as $method) {
        if (! method_exists(\App\Http\Controllers\Leader\KPIController::class, $method)) {
            $missingMethods[] = 'Leader\\KPIController::'.$method;
        }
    }
}
if (class_exists(\App\Http\Controllers\Leader\ReportController::class)) {
    foreach (['submit', 'show'] as $method) {
        if (! method_exists(\App\Http\Controllers\Leader\ReportController::class, $method)) {
            $missingMethods[] = 'Leader\\ReportController::'.$method;
        }
    }
}

$outputPath = __DIR__.'/../storage/app/leader-route-test.json';
file_put_contents($outputPath, json_encode([
    'leader' => $leader->only(['id', 'username', 'name']),
    'team_members' => Employee::query()->where('manager_id', $leader->employee?->id)->pluck('employee_code')->all(),
    'ok' => $ok,
    'warn' => $warn,
    'fail' => $fail,
    'missing_controllers' => $missingControllers,
    'missing_methods' => $missingMethods,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Report: {$outputPath}\n";
echo 'OK:'.count($ok).' WARN:'.count($warn).' FAIL:'.count($fail)."\n";
