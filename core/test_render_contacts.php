<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\System\Contact\ContactService;

$service = new ContactService();
$view = $service->getContacts(null, null);
$html = $view->render();

echo "RENDERED LENGTH: " . strlen($html) . " bytes\n";
if (str_contains($html, 'No Data Found')) {
    echo "CONTAINS 'No Data Found': YES!\n";
} else {
    echo "CONTAINS 'No Data Found': NO! (Data is rendered properly!)\n";
}
