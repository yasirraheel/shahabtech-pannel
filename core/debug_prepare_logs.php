<?php
$omnireachPath = '/home/u559276167/domains/shahabtech.com/public_html/omnireach/src';
require $omnireachPath . '/vendor/autoload.php';
$app = require $omnireachPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\System\Contact\ContactService;
use App\Enums\System\ChannelTypeEnum;
use App\Enums\System\SettingKey;
use Illuminate\Http\Request;
use App\Models\User;

$user = User::find(2);
$contactService = app(ContactService::class);

$contactsInput = '923006859611';
$type = ChannelTypeEnum::WHATSAPP;

$groups = $contactService->handleContacts(type: $type, contactsInput: $contactsInput, user: $user);
echo "GROUPS COUNT: " . count($groups) . "\n";
foreach ($groups as $g) {
    echo "GROUP ID: {$g->id} | NAME: {$g->name}\n";
}

$request = new Request([
    'contacts' => '923006859611',
    'message' => ['message_body' => 'TEST'],
    'method' => 'node',
    'gateway_id' => 8,
]);

$contactColumn = "whatsapp_contact";

$mapped = collect($groups)->map(function ($group) use ($contactColumn, $request) {
    $group = $group->load(["contacts"]);
    echo "LOADED CONTACTS FOR GROUP {$group->id}: " . $group->contacts->count() . "\n";
    if ($group->name == SettingKey::SINGLE_CONTACT_GROUP_NAME->value) {
         echo "IS SINGLE CONTACT GROUP NAME MATCH!\n";
         $sub = $group->contacts
             ->whereNotNull($contactColumn)
             ->when(!is_array($request->input("contacts")), fn($q) => 
                  $q->where($contactColumn, $request->input("contacts"))
                       ->take(1));
         echo "SUB COUNT: " . $sub->count() . "\n";
         return $sub;
    } else {
         return $group->contacts->whereNotNull($contactColumn);
    }
});

echo "MAPPED COUNT: " . $mapped->count() . "\n";
$flattened = $mapped->flatten(1);
echo "FLATTENED COUNT: " . $flattened->count() . "\n";
foreach ($flattened as $k => $v) {
    echo "ITEM {$k}: " . (is_object($v) ? get_class($v) . " (ID: {$v->id})" : json_encode($v)) . "\n";
}
