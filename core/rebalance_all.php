<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AccountListing;
use App\Constants\Status;

echo "Starting one-time re-balancing for all existing users...\n";

$users = User::whereNotNull('account_ids')->get();
$rebalancedCount = 0;

foreach ($users as $user) {
    $currentAccountIds = (array) ($user->account_ids ?? []);
    if (empty($currentAccountIds)) continue;

    $assignedListings = AccountListing::whereIn('id', $currentAccountIds)->get();
    $platformIds = $assignedListings->pluck('social_media_id')->unique()->toArray();

    if (empty($platformIds)) continue;

    $newAccountIds = [];
    foreach ($platformIds as $platformId) {
        $listings = AccountListing::where('social_media_id', $platformId)
            ->where('status', Status::LISTING_ACTIVE)
            ->get();

        if ($listings->isEmpty()) continue;

        $bestListing = null;
        $minUserCount = PHP_INT_MAX;

        foreach ($listings as $listing) {
            $count = User::whereJsonContains('account_ids', (int) $listing->id)
                ->orWhereJsonContains('account_ids', (string) $listing->id)
                ->count();

            if ($count < $minUserCount) {
                $minUserCount = $count;
                $bestListing = $listing;
            }
        }

        if ($bestListing) {
            $newAccountIds[] = $bestListing->id;
        }
    }

    $user->account_ids = array_values(array_unique($newAccountIds));
    $user->timestamps = false;
    $user->save();
    $user->timestamps = true;
    $rebalancedCount++;
}

echo "Successfully re-balanced $rebalancedCount existing users.\n";
