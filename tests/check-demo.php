<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'users='.App\Models\User::count().PHP_EOL;
foreach (['admin', 'manager', 'leader', 'anhlethanh', 'employee'] as $username) {
    echo $username.':'.(App\Models\User::where('username', $username)->exists() ? 'yes' : 'no').PHP_EOL;
}
