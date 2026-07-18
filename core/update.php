<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$item = \App\Models\Frontend::find(39);
$data = (array) $item->data_values;

$data['heading'] = "Shared Accounts Marketplace";
$data['subheading'] = "Connecting users to premium services affordably, our platform offers a secure and transparent marketplace for acquiring high-quality shared subscriptions and accounts.";

$item->data_values = clone (object) $data;
$item->save();

echo "Success";
