<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- USERS ---\n";
$users = DB::table('users')->get();
foreach ($users as $u) {
    echo "User ID: {$u->id} | Name: " . ($u->name ?? $u->username ?? 'N/A') . " | Email: {$u->email}\n";
}

echo "\n--- CONTACT GROUPS ---\n";
$groups = DB::table('contact_groups')->get();
echo "Total Groups: " . $groups->count() . "\n";
foreach ($groups as $g) {
    echo "ID: {$g->id} | UserID: " . json_encode($g->user_id) . " | Name: {$g->name} | Status: {$g->status}\n";
}

echo "\n--- CONTACTS SUMMARY ---\n";
$totalContacts = DB::table('contacts')->count();
echo "Total Contacts Count: {$totalContacts}\n";

$contactsByUser = DB::table('contacts')
    ->select('user_id', DB::raw('count(*) as total'))
    ->groupBy('user_id')
    ->get();
foreach ($contactsByUser as $c) {
    echo "UserID: " . json_encode($c->user_id) . " | Count: {$c->total}\n";
}

echo "\n--- CAMPAIGNS ---\n";
$campaigns = DB::table('campaigns')->get();
echo "Total Campaigns: " . $campaigns->count() . "\n";
foreach ($campaigns as $camp) {
    echo "ID: {$camp->id} | UserID: " . json_encode($camp->user_id) . " | Name: {$camp->name} | Channel: {$camp->type} | Status: {$camp->status}\n";
}
