<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\System\Contact\ContactService;
use App\Models\Contact;

$service = new ContactService();

// Test fetchContacts directly
$refMethod = new ReflectionMethod(ContactService::class, 'fetchContacts');
$refMethod->setAccessible(true);
$contacts = $refMethod->invoke($service, false, null, null);

echo "FETCH CONTACTS COUNT: " . $contacts->count() . "\n";
echo "TOTAL IN PAGINATOR: " . $contacts->total() . "\n";
