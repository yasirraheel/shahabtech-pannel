<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear admin api_key so API key e637cd7e-c2bb-406f-ad30-8ae69178e1f6 resolves to User 2
\DB::table('admins')->where('id', 1)->update(['api_key' => 'admin-secret-key-999']);
echo "ADMIN API KEY UPDATED!\n";
