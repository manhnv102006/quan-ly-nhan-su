<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('username', 'leader')->firstOrFail();
Illuminate\Support\Facades\Auth::login($user);

$uris = ['/leader/contracts', '/leader/kpis/1/score', '/leader/attendance', '/leader/team-kpis'];

foreach ($uris as $uri) {
    try {
        $response = $app->handle(Illuminate\Http\Request::create($uri, 'GET'));
        echo $uri.' => '.$response->getStatusCode()."\n";
        if ($response->getStatusCode() >= 500) {
            $body = $response->getContent();
            if (preg_match('/exception-title[^>]*>([^<]+)/', $body, $m)) {
                echo '  title: '.trim(html_entity_decode($m[1]))."\n";
            }
            if (preg_match('/exception-message[^>]*>([^<]+)/', $body, $m)) {
                echo '  msg: '.trim(html_entity_decode($m[1]))."\n";
            }
        }
    } catch (Throwable $e) {
        echo $uri.' => EXCEPTION: '.$e->getMessage()."\n";
    }
}
