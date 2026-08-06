<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "RUNNING OVERRIDE LOAD BALANCE FOR PLATFORM 2...\n";
App\Http\Controllers\Admin\SocialMediaController::executeLoadBalance(2, 'override_manual');

echo "\n=== RESULTING ACCOUNT COUNTS ===\n";
$accounts = App\Models\AccountListing::where('social_media_id', 2)->get();
foreach ($accounts as $acc) {
    $accId = (int) $acc->id;
    $allUsers = App\Models\User::where(function($q) use ($accId) {
        $q->whereJsonContains('account_ids', $accId)
          ->orWhereJsonContains('account_ids', (string) $accId);
    })->get();

    $activeCount = 0;
    foreach ($allUsers as $u) {
        if ($u->expires_at && $u->expires_at > now() && $u->status == App\Constants\Status::USER_ACTIVE) {
            $activeCount++;
        }
    }

    echo "ID {$acc->id} | {$acc->title} | CookieStatus: {$acc->cookie_status} | ACTIVE USERS: {$activeCount}\n";
}
