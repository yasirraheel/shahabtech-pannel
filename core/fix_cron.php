<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cron = App\Models\CronJob::find(4);
if ($cron) {
    $cron->next_run = now();
    $cron->action = ['\App\Http\Controllers\CronController', 'warzoneAutoBuyGemini'];
    $cron->is_default = 1;
    $cron->save();
    echo "SUCCESS: Cron 4 updated! Next run is: " . $cron->next_run . PHP_EOL;
} else {
    echo "Cron 4 not found." . PHP_EOL;
}
