<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SocialMedia;
use App\Models\AccountListing;
use App\Models\User;
use App\Constants\Status;

$platformId = 3;
$platform = SocialMedia::findOrFail($platformId);

$activeAccounts = AccountListing::where('social_media_id', $platform->id)
    ->where('status', Status::LISTING_ACTIVE)
    ->get();

$activeAccountIds = $activeAccounts->pluck('id')->toArray();
$allPlatformAccountIds = AccountListing::where('social_media_id', $platform->id)->pluck('id')->toArray();

$users = User::where('status', '!=', Status::USER_BAN)->get();

echo "Active Accounts for Platform 3: " . json_encode($activeAccountIds) . "\n";
echo "Total Active Users to process: " . $users->count() . "\n";

$accountUserCounts = [];
foreach ($activeAccountIds as $accId) {
    $accountUserCounts[(int) $accId] = 0;
}

$updatedUsersCount = 0;
foreach ($users as $user) {
    $currentAccountIds = array_map('intval', (array) ($user->account_ids ?? []));
    $otherAccountIds = array_diff($currentAccountIds, $allPlatformAccountIds);

    asort($accountUserCounts);
    $bestAccountId = key($accountUserCounts);

    $otherAccountIds[] = (int) $bestAccountId;
    $accountUserCounts[(int) $bestAccountId]++;

    $user->account_ids = array_values(array_unique($otherAccountIds));
    $user->timestamps = false;
    $user->save();
    $user->timestamps = true;

    $updatedUsersCount++;
}

echo "Updated {$updatedUsersCount} users.\n\n";
echo "=== NEW USER COUNTS PER ACCOUNT ===\n";
foreach ($activeAccounts as $acc) {
    $count = User::whereJsonContains('account_ids', (int) $acc->id)
        ->orWhereJsonContains('account_ids', (string) $acc->id)
        ->count();
    echo "Account #{$acc->id} ({$acc->title}): {$count} users assigned\n";
}
