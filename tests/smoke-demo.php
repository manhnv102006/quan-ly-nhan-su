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

$checks = [
    ['GET', '/login', null, 'Login page'],
    ['GET', '/leader/dashboard', null, 'Leader dashboard (guest)'],
];

foreach ($checks as [$method, $path, $body, $label]) {
    $result = request($method, $base.$path);
    echo sprintf("[%s] %s %s -> %d\n", $label, $method, $path, $result['status']);
}

$accounts = [
    'admin' => '/admin/dashboard',
    'manager' => '/manager/dashboard',
    'leader' => '/leader/dashboard',
    'employee' => '/employee/dashboard',
];

foreach ($accounts as $username => $dashboardPath) {
    $cookie = sys_get_temp_dir().DIRECTORY_SEPARATOR.'demo_'.$username.'.cookie';
    @unlink($cookie);

    $loginPage = request('GET', $base.'/login', null, [], $cookie);
    $token = extractToken($loginPage['body']);

    if (! $token) {
        echo "[LOGIN FAIL] $username: no CSRF token\n";
        continue;
    }

    $payload = http_build_query([
        '_token' => $token,
        'login' => $username,
        'password' => 'password',
    ]);

    $login = request('POST', $base.'/login', $payload, ['Content-Type: application/x-www-form-urlencoded'], $cookie);
    echo sprintf("[LOGIN] %s -> %d\n", $username, $login['status']);

    $paths = [
        $dashboardPath,
        str_starts_with($dashboardPath, '/leader') ? '/leader/employees' : null,
        str_starts_with($dashboardPath, '/leader') ? '/leader/contracts' : null,
        str_starts_with($dashboardPath, '/leader') ? '/leader/team-requests' : null,
        str_starts_with($dashboardPath, '/manager') ? '/manager/team-requests' : null,
    ];

    foreach (array_filter($paths) as $path) {
        $page = request('GET', $base.$path, null, [], $cookie);
        $ok = in_array($page['status'], [200, 302], true) && ! str_contains($page['body'], 'Server Error');
        $flag = $ok ? 'OK' : 'FAIL';
        if (str_contains($page['body'], 'Server Error') || str_contains($page['body'], 'ReflectionException')) {
            $flag = 'ERROR500';
        }
        echo sprintf("  [%s] %s -> %d\n", $flag, $path, $page['status']);
    }
}

echo "DONE\n";
