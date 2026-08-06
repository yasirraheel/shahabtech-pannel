<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$acc = App\Models\AccountListing::find(3);
echo "ACCOUNT: " . $acc->title . " (ID: " . $acc->id . ")\n\n";

$usersOnThree = App\Models\User::where(function($q) {
    $q->whereJsonContains('account_ids', 3)
      ->orWhereJsonContains('account_ids', "3");
})->get();

echo "TOTAL USERS HAVING ACC ID 3: " . $usersOnThree->count() . "\n";

foreach ($usersOnThree as $u) {
    $isTester = $u->is_tester;
    $status = $u->status;
    $ev = $u->ev;
    $sv = $u->sv;
    $exp = $u->expires_at ? $u->expires_at->format('Y-m-d H:i:s') : 'NULL';
    $isActiveScope = App\Models\User::active()->where('id', $u->id)->exists();

    echo "User #{$u->id} ({$u->username}) | Email: {$u->email} | Tester: {$isTester} | Status: {$status} | EV: {$ev} | SV: {$sv} | Expiry: {$exp} | InActiveScope: " . ($isActiveScope ? 'YES' : 'NO') . "\n";
}
