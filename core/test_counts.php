<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ACCOUNTS DETAILS ===\n";
$accounts = App\Models\AccountListing::with('socialMedia')->get();

foreach ($accounts as $acc) {
    $accId = (int) $acc->id;
    
    // Total users having this account ID in account_ids
    $allUsersHavingAcc = App\Models\User::where(function($q) use ($accId) {
        $q->whereJsonContains('account_ids', $accId)
          ->orWhereJsonContains('account_ids', (string) $accId);
    })->get();

    $activeCount = 0;
    $expiredCount = 0;
    $bannedCount = 0;

    foreach ($allUsersHavingAcc as $u) {
        $isBanned = $u->status == App\Constants\Status::USER_BAN;
        $isExpired = !$u->expires_at || $u->expires_at <= now();

        if ($isBanned) {
            $bannedCount++;
        } elseif ($isExpired) {
            $expiredCount++;
        } else {
            $activeCount++;
        }
    }

    $platformName = $acc->socialMedia->name ?? 'N/A';
    echo "ID {$acc->id} | {$acc->title} ({$platformName}) | Status: {$acc->status} | CookieStatus: {$acc->cookie_status} | TOTAL: {$allUsersHavingAcc->count()} (Active: {$activeCount}, Expired: {$expiredCount}, Banned: {$bannedCount})\n";
}

echo "\n=== ALL USERS DETAILS ===\n";
$users = App\Models\User::all();
foreach ($users as $u) {
    $exp = $u->expires_at ? $u->expires_at->format('Y-m-d H:i') : 'NULL';
    $isExpStr = (!$u->expires_at || $u->expires_at <= now()) ? '[EXPIRED]' : '[ACTIVE]';
    echo "USER #{$u->id} ({$u->username}) | {$isExpStr} | Expiry: {$exp} | AccIDs: " . json_encode($u->account_ids) . "\n";
}
