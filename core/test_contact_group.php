<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
$cService = new App\Services\System\Contact\ContactService();
try {
    $group = $cService->createSingleContactGroup(App\Enums\System\ChannelTypeEnum::WHATSAPP, '923006859611', $user);
    echo "SINGLE CONTACT GROUP CREATED/FOUND: " . json_encode($group) . "\n";
} catch (\Throwable $e) {
    echo "ERROR IN createSingleContactGroup: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
