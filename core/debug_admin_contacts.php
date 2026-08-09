<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

// Simulate Admin Login (auth('admin') = 1)
$admin = \App\Models\Admin::first();
auth('admin')->login($admin);

echo "LOGGED IN ADMIN ID: " . auth('admin')->id() . "\n";
echo "DEFAULT AUTH ID: " . json_encode(auth()->id()) . "\n";

$contactsQuery = Contact::admin();
echo "SQL QUERY: " . $contactsQuery->toSql() . "\n";
echo "SQL BINDINGS: " . json_encode($contactsQuery->getBindings()) . "\n";

$results = $contactsQuery->get();
echo "FETCHED CONTACTS COUNT: " . $results->count() . "\n";
