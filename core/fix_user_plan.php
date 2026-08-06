<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(2);
if ($user) {
    // Inspect user attributes
    echo "USER ATTRIBUTES:\n";
    print_r($user->getAttributes());
    
    // Update plan expiration date and status
    \DB::table('users')->where('id', 2)->update([
        'plan_id' => 1,
        'plan_expired_at' => '2030-12-31 23:59:59',
    ]);
    echo "\nUSER 2 PLAN EXPIRATION EXTENDED TO 2030!\n";
}
