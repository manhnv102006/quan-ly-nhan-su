<?php
$base = 'http://127.0.0.1:8000';
$cookie = tempnam(sys_get_temp_dir(), 'acc');
function req($m, $u, $b = null, $c = null) {
    global $base;
    $ch = curl_init($base.$u);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_HEADER => 1, CURLOPT_FOLLOWLOCATION => 0, CURLOPT_COOKIEJAR => $c, CURLOPT_COOKIEFILE => $c]);
    if ($b) { curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, CURLOPT_POSTFIELDS, $b); }
    $r = curl_exec($ch);
    $s = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$s, $r];
}
preg_match('/name="_token" value="([^"]+)"/', req('GET', '/login', null, $cookie)[1], $m);
$payload = http_build_query(['_token' => $m[1], 'login' => 'accountant', 'password' => 'password']);
req('POST', '/login', $payload, $cookie);
[$s, $b] = req('GET', '/accountant/dashboard', null, $cookie);
echo "status=$s error=".(str_contains($b, 'Server Error') ? 'YES' : 'NO')."\n";
if ($s === 302 && preg_match('/Location: ([^\r\n]+)/', $b, $loc)) {
    echo 'redirect='.trim($loc[1])."\n";
    [$s2, $b2] = req('GET', trim($loc[1]), null, $cookie);
    echo "after redirect status=$s2 error=".(str_contains($b2, 'Server Error') ? 'YES' : 'NO')."\n";
}
