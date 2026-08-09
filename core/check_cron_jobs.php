<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CronJob;
use App\Models\CronJobLog;

echo "--- CRON JOBS ---\n";
$jobs = CronJob::all();
foreach ($jobs as $j) {
    echo "ID: {$j->id} | Name: {$j->name} | Alias: {$j->alias} | IsRunning: {$j->is_running} | LastRun: {$j->last_run} | NextRun: {$j->next_run}\n";
}

echo "\n--- RECENT CRON LOGS (Last 10) ---\n";
$logs = CronJobLog::orderBy('id', 'desc')->take(10)->get();
foreach ($logs as $l) {
    echo "ID: {$l->id} | JobID: {$l->cron_job_id} | Start: {$l->start_at} | Duration: {$l->duration}s | Error: {$l->error}\n";
}
