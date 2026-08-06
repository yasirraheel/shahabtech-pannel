<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$settings = \DB::table('settings')->get();
echo "SETTINGS IN OMNIREACH:\n";
foreach ($settings as $s) {
    if (str_contains($s->slug, 'gateway') || str_contains($s->slug, 'whatsapp')) {
        echo "SLUG: {$s->slug} | VALUE: " . substr(json_encode($s->value), 0, 100) . "\n";
    }
}
