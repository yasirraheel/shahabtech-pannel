<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Campaign;

echo "--- ADMIN QUERY (user_id IS NULL) ---\n";
echo "Groups Count: " . ContactGroup::whereNull('user_id')->count() . "\n";
echo "Contacts Count: " . Contact::whereNull('user_id')->count() . "\n";
echo "Campaigns Count: " . Campaign::whereNull('user_id')->count() . "\n";

echo "\n--- USER 2 QUERY (user_id = 2) ---\n";
echo "Groups Count: " . ContactGroup::where('user_id', 2)->count() . "\n";
echo "Contacts Count: " . Contact::where('user_id', 2)->count() . "\n";
echo "Campaigns Count: " . Campaign::where('user_id', 2)->count() . "\n";
