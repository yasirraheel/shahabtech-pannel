<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::all();
echo "TOTAL USERS IN OMNIREACH: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "ID: {$u->id} | Username: {$u->username} | Email: {$u->email} | API Key: {$u->api_key}\n";
}
