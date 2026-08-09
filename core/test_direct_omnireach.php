<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;

$adminContacts = Contact::withoutGlobalScopes()->whereNull('user_id')->get();
echo "WITHOUT GLOBAL SCOPES (user_id IS NULL): " . $adminContacts->count() . "\n";

$adminContactsScoped = Contact::whereNull('user_id')->get();
echo "WITH GLOBAL SCOPE (user_id IS NULL): " . $adminContactsScoped->count() . "\n";
