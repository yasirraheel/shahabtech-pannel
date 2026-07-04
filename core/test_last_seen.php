<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = App\Models\User::where('username', 'boseong')->first();
echo "Last Seen: " . ($user->last_seen ?? 'NULL') . "\n";
