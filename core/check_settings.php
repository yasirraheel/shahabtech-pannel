<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$settings = \DB::table('site_settings')->where('key', 'like', '%gateway%')->orWhere('key', 'like', '%whatsapp%')->get();
echo "SITE SETTINGS:\n";
foreach ($settings as $s) {
    echo "KEY: {$s->key} | VALUE: {$s->value}\n";
}
