<?php

declare(strict_types=1);

$base = 'http://127.0.0.1:8000';

function request(string $method, string $url, ?string $body = null, array $headers = [], ?string $cookieFile = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    if ($headers !== []) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => $response ?: ''];
}

function extractToken(string $html): ?string
{
    if (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }

    return null;
}

function login(string $username): ?string
{
    global $base;
    $cookie = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ext_'.$username.'.cookie';
    @unlink($cookie);

    $loginPage = request('GET', $base.'/login', null, [], $cookie);
    $token = extractToken($loginPage['body']);
    if (! $token) {
        echo "[LOGIN FAIL] $username\n";

        return null;
    }

    $payload = http_build_query([
        '_token' => $token,
        'login' => $username,
        'password' => 'password',
    ]);

    request('POST', $base.'/login', $payload, ['Content-Type: application/x-www-form-urlencoded'], $cookie);

    return $cookie;
}

function check(?string $cookie, string $path, string $label): void
{
    global $base;
    if (! $cookie) {
        return;
    }

    $page = request('GET', $base.$path, null, [], $cookie);
    $error = str_contains($page['body'], 'Server Error')
        || str_contains($page['body'], 'ReflectionException')
        || str_contains($page['body'], 'RouteNotFoundException');
    $ok = in_array($page['status'], [200, 302], true) && ! $error;
    echo sprintf("[%s] %s -> %d %s\n", $ok ? 'OK' : 'FAIL', $label, $page['status'], $path);
}

$scenarios = [
    'admin' => [
        '/admin/dashboard',
        '/admin/early-leave',
        '/admin/contracts',
        '/admin/employees',
    ],
    'manager' => [
        '/manager/dashboard',
        '/manager/employees',
        '/manager/early-leave',
        '/manager/kpis',
        '/manager/leave-requests',
        '/manager/overtime-requests',
        '/manager/team-requests',
    ],
    'leader' => [
        '/leader/dashboard',
        '/leader/employees',
        '/leader/contracts',
        '/leader/kpis',
        '/leader/team-requests',
        '/leader/tasks',
        '/leader/reports',
    ],
    'employee' => [
        '/employee/dashboard',
        '/employee/early-leave',
        '/employee/leave-requests',
        '/employee/overtime-requests',
        '/employee/kpis',
        '/employee/contracts',
        '/employee/payrolls',
        '/employee/attendance',
    ],
    'accountant' => [
        '/accountant/dashboard',
        '/accountant/payrolls',
        '/accountant/payroll-periods',
        '/accountant/advances',
    ],
];

foreach ($scenarios as $user => $paths) {
    echo "\n=== $user ===\n";
    $cookie = login($user);
    foreach ($paths as $key => $path) {
        if (is_string($key)) {
            $path = $path;
        }
        check($cookie, $path, $user);
    }
}

echo "\nDONE\n";
