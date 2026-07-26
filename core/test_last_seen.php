<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Carbon\Carbon;

$user = User::first();
if ($user) {
    echo "Before: last_seen = " . ($user->last_seen ? $user->last_seen->toDateTimeString() : 'NULL') . ", total_online_time = " . ($user->total_online_time ?? 0) . "\n";
    
    // Simulate updating last_seen
    $now = now();
    if (!$user->last_seen) {
        $user->last_seen = $now;
    } else {
        $lastSeen = Carbon::parse($user->last_seen);
        $diff = $lastSeen->diffInSeconds($now);
        echo "Diff in seconds: " . $diff . "\n";
        if ($diff >= 0 && $diff <= 900) {
            $user->total_online_time = (int)$user->total_online_time + $diff;
        }
        $user->last_seen = $now;
    }
    $user->timestamps = false;
    $user->save();
    $user->timestamps = true;
    
    $user->refresh();
    echo "After: last_seen = " . ($user->last_seen ? $user->last_seen->toDateTimeString() : 'NULL') . ", total_online_time = " . ($user->total_online_time ?? 0) . ", formatted = " . $user->onlineTimeFormatted() . "\n";
} else {
    echo "No user found\n";
}
