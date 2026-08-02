<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TOTAL USERS IN DB ===\n";
$users = App\Models\User::all();
echo "Total Users: " . $users->count() . "\n\n";

foreach ($users as $u) {
    echo "User #{$u->id} | {$u->username} | Status: {$u->status} | Plan ID: {$u->plan_id} | Expires: {$u->expires_at} | Account IDs: " . json_encode($u->account_ids) . "\n";
}

echo "\n=== ALL PLATFORMS ===\n";
foreach (App\Models\SocialMedia::all() as $p) {
    echo "Platform #{$p->id} | Name: {$p->name} | Domain: {$p->domain}\n";
    $accs = App\Models\AccountListing::where('social_media_id', $p->id)->get();
    foreach ($accs as $a) {
        echo "   -> Account #{$a->id} | Title: {$a->title} | Status: {$a->status} | Plan ID: {$a->plan_id}\n";
    }
}
